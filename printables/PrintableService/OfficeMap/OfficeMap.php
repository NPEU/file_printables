<?php
namespace PrintableService\OfficeMap;

/**
 * OfficeMap
 *
 * Generate a PDF of Office Cards (name labels) for all rooms, or an individual room if param given.
 *
 * @package PrintableService
 * @author akirk
 * @copyright Copyright (c) 2021 NPEU
 * @version 0.1

 **/

use setasign\Fpdi\Tcpdf\Fpdi;

class OfficeMap extends \PrintableService\PrintableService
{

	/*=public function __construct()
	{
		parent::__construct();

	}*/

	public function run()
	{
        $db = \JFactory::getDbo();

        #echo '<pre>'; var_dump($db); echo '</pre>'; exit;

        // Create the select statement.
        $q = 'SELECT params FROM #__modules WHERE id = 511;';

        $db->setQuery($q);
        if (!$db->execute($q)) {
            JError::raiseError( 500, $db->stderr() );
            return false;
        }

        $mod_params = $db->loadResult();

        $params = \Joomla\Registry\Registry::getInstance('');
        $params->loadString($mod_params);

        $svg = trim($params->get('svg'));

        $staff_members = $this->getStaffData();

        // Process the data:

        // It's friendlier to show first names, but there are sometimes conflicts (Jenny, Andy):
        $first_names    = [];
        $friendly_names = [];
        $rooms          = [];
        $room_to_names  = [];


        foreach ($staff_members as $k => $staff) {

            $first_name = trim($staff['first_name']);
            if (!array_key_exists($first_name, $first_names)) {
                $first_names[$first_name] = 0;
            }
            $first_names[$first_name]++;



            $lastname = trim($staff['last_name']);
            $t = str_replace(' ', '-', $lastname);

            if (strpos($t, '-') !== false) {
                $lastname_initials = '';

                $parts = explode('-', $t);
                foreach($parts as $part) {
                    $lastname_initials .= $part[0];
                }
            } else {
                $lastname_initials = $staff['last_name'][0];
            }

            $friendly_name = trim($staff['first_name']) . ' ' . $lastname_initials;
            if (!array_key_exists($friendly_name, $friendly_names)) {
                $friendly_names[$friendly_name] = 0;
            }
            $friendly_names[$friendly_name]++;
            $staff_members[$k]['friendly_name'] = $friendly_name;

            // Check the avatar while we're here:
            if (empty($staff['avatar'])) {
                $staff_members[$k]['avatar'] = '/assets/images/avatars/_none.jpg';
            }



            $room = empty($staff['room']) ? 'unassigned' : $staff['room'];
            if (!array_key_exists($room, $rooms)) {
                $rooms[$room] = [];
            }
            $rooms[$room][] = $k;
            $room_to_names[$room][] = $staff['name'];
        }
        #echo '<pre>'; var_dump($staff_members); echo '</pre>'; exit;
        #echo '<pre>'; var_dump($room_to_names); echo '</pre>'; exit;
        // 2nd pass to add the <tspan> elements to the svg:

        foreach ($rooms as $room => $keys) {
            $s = '';
            $y = 0;
            foreach ($keys as $k) {

                $staff_member = $staff_members[$k];
                $name = trim($staff_member['first_name']);

                if ($first_names[$name] > 1) {

                    $name = $staff_member['friendly_name'];
                    if ($friendly_names[$name] > 1) {
                        $name = trim($staff_member['name']);
                    }
                }

                $s .= '<a href="#' . $staff_member['alias'] . '"><tspan x="0" y="' . $y . '" class="st12 st5 st13">' . $name . '</tspan></a>';
                $y += 7.8;
            }

            $svg = str_replace($room, $s, $svg);
        }

        // We need to fiddle with the SVG to make it suitable for print:
        preg_match('#viewBox="([^"]*)"#', $svg, $matches);
        $viewbox = explode(' ', $matches[1]);
        $svg_width     = $viewbox[2];
        $svg_width_mm  = $svg_width * 0.352778;
        $svg_height    = $viewbox[3];
        $svg_height_mm = $svg_height * 0.352778;

        $svg = str_replace($matches[0], $matches[0] . ' width="' . $svg_width . 'px"', $svg);
        #echo '<pre>'; var_dump($viewbox); echo '</pre>'; exit;

        $filemame = 'NPEU Office Map.pdf';

        $line_height  = 11; //pt
        $page_width   = 297; //mm
        $page_height  = 210; //mm
        $block_margin = 10; //mm
        #$block_gutter = 6; //mm
        #$block_width  = 92; //mm

        #$border_width = 0.5; //pt
        #$cell_padding = 1.8; //pt

        #$top = 28;


        $pdf = new Fpdi('L','mm', array($page_width, $page_height));

        #$pdf->setFontSubsetting(true);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->SetMargins($block_margin, $block_margin, $block_margin);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);

        $pdf->SetAutoPageBreak(false);

        $pdf->SetTextColor(0, 0, 0);
        #$pdf->SetFont('arial', '', 10);
        $pdf->SetFont('lato', '', 10);
        $pdf->setFontSubsetting(false);

        $pdf->setSourceFile(__DIR__ . "/office_map_template.pdf");
        $tpl_idx = $pdf->importPage(1);

        $pdf->addPage();
        $pdf->useTemplate($tpl_idx, 0, 0);

        
        $tmpfile = tmpfile();
        fwrite($tmpfile, $svg);
        $tmpfile_path = stream_get_meta_data($tmpfile)['uri'];
        #fseek($temp, 0);
        #echo fread($temp, 1024);
        #echo file_get_contents($tmpfile_path); exit;

        // Add the SVG as an image:
        
        $svg_x = ($page_width - $svg_width_mm) / 2;
        $svg_y = 22;
        // $x='', $y='', $w=0, $h=0, $link='', $align='', $palign='', $border=0, $fitonpage=false
        $pdf->ImageSVG($tmpfile_path, $svg_x, $svg_y, '', '', '',   '', '', 0, false);


        // Date stamp:
        $date_y = $page_height - $block_margin;
        $pdf->SetFontSize(9);
        $pdf->SetTextColor(50);
        // $x, $y, $txt, $fstroke=false, $fclip=false, $ffill=true, $border=0, $ln=0, $align='', $fill=false, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M', $rtloff=false
        $pdf->Text('', $date_y, 'Generated: ' . date('d M Y'), false, false, true, 0, 0, 'R');


        $pdf->Output($filemame, 'I');
        
        fclose($temp); // this removes the file
        
        
        return true;
	}
}