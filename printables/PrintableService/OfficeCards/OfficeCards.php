<?php
namespace PrintableService\OfficeCards;

/**
 * OfficeCards
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

        

        $staff_members = $this->getStaffData();
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

        $filemame = 'NPEU Office Labels.pdf';

        $line_height  = 10;
        $page_width   = 145;
        $page_height  = 90;
        $block_margin = 8;

        $block_width  = $page_width - ($block_margin * 2);
        //$block_height = $page_height - ($block_margin * 2);

        $pdf = new Fpdi('L','mm', array($page_width, $page_height));

        #$pdf->setFontSubsetting(true);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->SetMargins($block_margin, 0, $block_margin);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);

        $pdf->SetAutoPageBreak(false);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('lato', '', 20);
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
            
            $h_top = 0;
            
            $html1 = $head;
            $html1 .= '<h1 style="font-family: nunitob; font-size: 30pt; line-height: ' . $line_height / 1.5 . 'mm;">' . $room . '</h1>';
            $html1 .= $tail;
            
            $pdf->writeHTMLCell($block_width, '', $block_margin, $h_top, $html1, 0, 0, 0, true, '', false);
            #echo $html1;

            $block_height = (count($names) * $line_height);
            $top = ($page_height - $block_height) / 2 + ($line_height / 4);

            #$html = str_replace('{{ padding-top }}', $top, $head);
            $html2  = $head;
            $html2 .= implode('<br>', $names);
            $html2 .= $tail;

            $pdf->writeHTMLCell($block_width, $block_height, $block_margin, $top, $html2, 0, 1, 0, true, '', true);
            #echo $html2;
        }


        $pdf->Output($filemame, 'I');

        return true;
	}
}