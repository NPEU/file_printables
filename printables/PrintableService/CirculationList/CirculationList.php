<?php
namespace PrintableService\CirculationList;

/**
 * CirculationList
 *
 * Generate a PDF of Office Cards (name labels) for all rooms, or an individual room if param given.
 *
 * @package PrintableService
 * @author akirk
 * @copyright Copyright (c) 2021 NPEU
 * @version 0.1

 **/

use setasign\Fpdi\Tcpdf\Fpdi;

class CirculationList extends \PrintableService\PrintableService
{
    /*protected $param_defs = [
        'room' => '#^l(0|1)-\d\d$#'
    ];*/

	/*=public function __construct()
	{
		parent::__construct();

	}*/

	public function run()
	{

        /*$single_room = false;
        if (!empty($this->valid_params['room'])) {
            $single_room = str_replace('l', 'L', str_replace('-', '/', $this->valid_params['room']));
        }*/
        #echo '<pre>'; var_dump($room); echo '</pre>'; exit;

        $staff_members = $this->getStaffData();
        #echo '<pre>'; var_dump($staff_members); echo '</pre>'; exit;

        $row_split = 38;

        $filemame = 'NPEU Circulation List.pdf';

        $line_height  = 10; //pt
        $page_width   = 210; //mm
        $page_height  = 297; //mm
        $block_margin = 10; //mm
        $block_gutter = 6; //mm
        $block_width  = 92; //mm

        $border_width = 0.5; //pt
        $cell_padding = 1.8; //pt

        $top = 36;


        $pdf = new Fpdi('P','mm', array($page_width, $page_height));

        #$pdf->setFontSubsetting(true);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->SetMargins($block_margin, $block_margin, $block_margin);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);

        $pdf->SetAutoPageBreak(false);

        $pdf->SetTextColor(0, 0, 0);
        #$pdf->SetFont('arial', '', 10);
        $pdf->SetFont('lato', '', 9);
        $pdf->setFontSubsetting(false);

        $pdf->setSourceFile(__DIR__ . "/circulation_list_template.pdf");
        $tpl_idx = $pdf->importPage(1);

        $pdf->addPage();
        $pdf->useTemplate($tpl_idx, 0, 0);


        $head = '<table border="0" cellspacing="4" cellpadding="' . $cell_padding .'pt" width="' . $block_width . 'mm" style="line-height: ' . $line_height . 'pt;">';
        $tail = '</table>';
        $row  = '<tr style="background-color: #%s;"><td style="background-color: #fff;" width="' . ($line_height + ($cell_padding * 2)) . 'pt" border="' . $border_width . 'pt">&nbsp;</td><td width="27mm">&nbsp;%s</td><td width="38mm"><b style="font-family: latob;">&nbsp;%s</b></td></tr>';

        list($col1, $col2) = array_chunk($staff_members, $row_split);
        //list($col1, $col2) = array_chunk($staff_members, ceil(count($array) / 2));


        $html1 = $head;
        $i = 0;
        foreach ($col1 as $staff) {
            $i++;
            $first_name = $staff['first_name'];
            $last_name  = strtoupper($staff['last_name']);

            $tel = trim($staff['tel']);
            if ($tel == '') {
                $tel = '<i style="color: red;">TBC</i>';
            }

            $stripe = ($i % 2 == 0) ? 'eee' : 'fff';

            $html1 .= sprintf($row, $stripe, $first_name, $last_name, $tel);
        }
        $html1 .= $tail;

        #echo $html1;
        $pdf->writeHTMLCell(0, 0, '', $top, $html1, 0, 1, 0, true, '', true);


        $html2 = $head;
        $i = 0;
        foreach ($col2 as $staff) {
            $i++;
            $first_name = $staff['first_name'];
            $last_name  = strtoupper($staff['last_name']);

            $stripe = ($i % 2 == 0) ? 'eee' : 'fff';

            $html2 .= sprintf($row, $stripe, $first_name, $last_name);
        }
        $html2 .= $tail;

        #echo $html2; exit;
        $left = $block_margin + $block_width + $block_gutter;
        #echo $left; exit;
        $pdf->writeHTMLCell(0, 0, $left, $top, $html2, 0, 1, 0, true, '', true);

        // Date stamp:
        $date_y = $page_height - $block_margin;
        $pdf->SetFontSize(9);
        $pdf->SetTextColor(50);
        // $x, $y, $txt, $fstroke=false, $fclip=false, $ffill=true, $border=0, $ln=0, $align='', $fill=false, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M', $rtloff=false
        $pdf->Text('', $date_y, 'Generated: ' . date('d M Y'), false, false, true, 0, 0, 'R');


        $pdf->Output($filemame, 'I');

        return true;
	}
}