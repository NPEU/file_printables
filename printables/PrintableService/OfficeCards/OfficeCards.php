<?php
namespace PrintableService\OfficeCards;

/**
 * OffficeCards
 *
 * Generate a PDF of Office Cards (name labels) for all rooms, or an individual room if param given.
 *
 * @package PrintableService
 * @author akirk
 * @copyright Copyright (c) 2021 NPEU
 * @version 0.1

 **/

use setasign\Fpdi\Tcpdf\Fpdi;

class OfficeCards extends \PrintableService\PrintableService
{
    protected $param_defs = [
        'room' => '#^l(0|1)-\d\d$#'
    ];

	/*=public function __construct()
	{
		parent::__construct();

	}*/

	public function run()
	{
        $single_room = false;
        if (!empty($this->valid_params['room'])) {
            $single_room = str_replace('l', 'L', str_replace('-', '/', $this->valid_params['room']));
        }
        #echo '<pre>'; var_dump($room); echo '</pre>'; exit;

        $db = \JFactory::getDbo();

        #echo '<pre>'; var_dump($db); echo '</pre>'; exit;


        // Create the select statement.
        $q  = 'SELECT u.id, u.name, u.email, up1.profile_value AS first_name, up2.profile_value AS last_name, up3.profile_value AS tel, up4.profile_value AS room, up5.profile_value AS alias, up6.profile_value AS avatar , up7.profile_value AS role ';
        $q .= 'FROM `#__users` u ';
        $q .= 'JOIN `#__user_usergroup_map` ugm ON u.id = ugm.user_id ';
        $q .= 'JOIN `#__usergroups` ug ON ugm.group_id = ug.id ';
        $q .= 'JOIN `#__user_profiles` up1 ON u.id = up1.user_id AND up1.profile_key = "firstlastnames.firstname" ';
        $q .= 'JOIN `#__user_profiles` up2 ON u.id = up2.user_id AND up2.profile_key = "firstlastnames.lastname" ';
        $q .= 'JOIN `#__user_profiles` up3 ON u.id = up3.user_id AND up3.profile_key = "staffprofile.tel" ';
        $q .= 'JOIN `#__user_profiles` up4 ON u.id = up4.user_id AND up4.profile_key = "staffprofile.room" ';
        $q .= 'JOIN `#__user_profiles` up5 ON u.id = up5.user_id AND up5.profile_key = "staffprofile.alias" ';
        $q .= 'JOIN `#__user_profiles` up6 ON u.id = up6.user_id AND up6.profile_key = "staffprofile.avatar_img" ';
        $q .= 'JOIN `#__user_profiles` up7 ON u.id = up7.user_id AND up7.profile_key = "staffprofile.role" ';
        $q .= 'WHERE ug.title = "Staff" ';
        $q .= 'AND u.block = 0 ';
        $q .= 'ORDER BY last_name, first_name;';

        $db->setQuery($q);
        if (!$db->execute($q)) {
            JError::raiseError( 500, $db->stderr() );
            return false;
        }

        $staff_members = $db->loadAssocList();
        #echo '<pre>'; var_dump($staff_members); echo '</pre>'; exit;
        // Process the data:

        // It's friendlier to show first names, but there are sometimes conflicts (Jenny, Andy):
        $rooms          = [];
        $room_to_names  = [];

        foreach ($staff_members as $k => $staff) {

            $room = empty($staff['room']) ? 'unassigned' : $staff['room'];
            if (!array_key_exists($room, $rooms)) {
                $rooms[$room] = [];
            }
            $rooms[$room][] = $k;
            $room_to_names[$room][] = $staff['name'];
        }

        ksort($room_to_names);
        #echo '<pre>'; var_dump($room_to_names); echo '</pre>'; exit;

        $filemame = 'Office Labels.pdf';
        #$tmpname  = tmpfile();
/*
        $return = [
            'filemame' => $filemame,
            'tmpname'  => $tmpname
        ];


*/
        $line_height  = 8;
        $page_width  = 148;
        $page_height = 105;
        $block_margin = 10;

        $block_width  = $page_width - ($block_margin * 2);
        //$block_height = $page_height - ($block_margin * 2);

        $pdf = new Fpdi('L','mm', array($page_width, $page_height));

        $pdf->setFontSubsetting(true);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->SetMargins($block_margin, $block_margin, $block_margin);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);

        $pdf->SetAutoPageBreak(false);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('arial', '', 16);
        $pdf->setFontSubsetting(false);

        $pdf->setSourceFile(__DIR__ . "/office_label_template.pdf");
        $tpl_idx = $pdf->importPage(1);


        $head = '<div style="line-height: ' . $line_height . 'mm; width: ' . $block_width . 'mm; text-align: center;">';
        $tail  = '</div>';

        if ($single_room && !array_key_exists($single_room, $room_to_names)) {
            return false;
        }

        foreach ($room_to_names as $room => $names) {

            if ($room == 'unassigned') {
                continue;
            }

            // If there's a room specified, skip this rooms if it's not the one specified.
            if ($single_room && $room != $single_room) {
                continue;
            }

            $pdf->addPage();
            $pdf->useTemplate($tpl_idx, 0, 0);

            $block_height = (count($names) + 2) * $line_height;
            $top = ($page_height - $block_height) / 2;

            #$html = str_replace('{{ padding-top }}', $top, $head);
            $html = $head;
            $html .= '<h1 style="margin: 0; padding: 0; font-size: 24pt; line-height: ' . ($line_height / 2) . 'mm;">' . $room . '</h1>';


            $html .= implode('<br>', $names);

            $html .= $tail;

            $pdf->writeHTMLCell($block_width, $block_height, $block_margin, $top, $html, 0, 1, 0, true, '', true);

            #echo $html;
        }


        $pdf->Output($filemame, 'I');

        return true;
	}
}