<?php
/**
 * OffficeCards
 *
 * {DESCRIPTION}
 *
 * @package PrintableService
 * @author akirk
 * @copyright Copyright (c) 2021 NPEU
 * @version 0.1

 **/
class OfficeCards extends \PrintableService
{
    /*protected $valid_params = [
        'room' => '#^l(0|1)-\d\d$#'
    ];*/

	/*=public function __construct()
	{
		parent::__construct();
		
	}*/
    
	public function run()
	{
        echo 'here'; exit;
        /*
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

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
        echo '<pre>'; var_dump($staff_members); echo '</pre>'; exit;
        #echo '<pre>'; var_dump($room_to_names); echo '</pre>'; exit;
        
        /*$filemame = 'Office Labels';
        $tmpname  = tmpfile();
        
        $return = [
            'filemame' => $filemame,
            'tmpname'  => $tmpname;
        ];
        
        
        
        
        $pdf = new Fpdi();

        $pdf->SetMargins(15, 50, 15);
        $pdf->setFontSubsetting(true);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);


        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('arial', '', 11);
        $pdf->setFontSubsetting(false);

        $pdf->setSourceFile(__DIR__ . "/office_label_template.pdf");
        $tpl_idx = $pdf->importPage(1);
   
        
        $head  = '<table border="0" cellspacing="0" cellpadding="0" style="width: 100%; height="100%">';
        $head .= '<tr><td style="vertical-align: middle; text-align: center;">';
        $tail  = '</td></tr></table>';

        foreach ($room_to_names as $room => $names) {
            
            // If there's a room specified, skip this rooms if it's not the one specified.
            // @todo

            $pdf->addPage();
            $pdf->useTemplate($tpl_idx, 0, 0);
            
            $html = $head;
        
            foreach ($names as $name) {
                $html .= $name . '<br>';
            }
            
            $html .= $tail;

            $pdf->writeHTMLCell(0, 0, '', '', $names, 0, 1, 0, true, '', true);
        }

        //$pdf->Output($tmpname, 'F');
        return $pdf->Output($filemame, 'D');
        
        
        //return $return;*/
	}
}