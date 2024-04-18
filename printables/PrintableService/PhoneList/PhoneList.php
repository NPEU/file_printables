<?php
namespace PrintableService\PhoneList;

/**
 * PhoneList
 *
 * Generate a PDF of Office Cards (name labels) for all rooms, or an individual room if param given.
 *
 * @package PrintableService
 * @author akirk
 * @copyright Copyright (c) 2021 NPEU
 * @version 0.1

 **/

use setasign\Fpdi\Tcpdf\Fpdi;

class PhoneList extends \PrintableService\PrintableService
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

        // This may come from elsewhere in the future:
        $useful_numbers = [
            "First Floor Meeting Room"        => "(6) 17777",
            "Ground Floor Meeting Room"       => "(2) 89734",
            "MBRRACE Office"                  => "(2) 89715",
            "Miles Beaumont (NDPH Accounts)"  => "(7) 43551",
            "NDPH IT Hub (IT support)"        => "(7) 43813",
            "NPEU Reception"                  => "(2) 89700",
            "Richard Doll Building Reception" => "(9) 743660",
            "Security"                        => "(2) 72944",
            "Trials Fax (L1/44-45)"           => "(2) 89740"
        ];



        /*$single_room = false;
        if (!empty($this->valid_params['room'])) {
            $single_room = str_replace('l', 'L', str_replace('-', '/', $this->valid_params['room']));
        }*/
        #echo '<pre>'; var_dump($room); echo '</pre>'; exit;
        $staff_members = $this->getStaffData();
        #echo '<pre>'; var_dump($staff_members); echo '</pre>'; exit;

        $row_split = 53;

        $filemame = 'NPEU Phone List.pdf';

        $font_size  =   7; //pt
        $line_height  = 10.5; //pt
        $page_width   = 210; //mm
        $page_height  = 297; //mm
        $block_margin = 8; //mm
        $block_gutter = 6; //mm
        $block_width  = 92; //mm

        $border_width = 0.5; //pt
        $cell_padding = 1.1; //pt

        $top = 34;


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
        $pdf->SetFont('lato', '', 10);
        $pdf->setFontSubsetting(false);

        $pdf->setSourceFile(__DIR__ . "/phone_list_template.pdf");
        $tpl_idx = $pdf->importPage(1);

        $pdf->addPage();
        $pdf->useTemplate($tpl_idx, 0, 0);


        $head = '<table border="' . $border_width . 'pt" cellspacing="0" cellpadding="' . $cell_padding .'pt" width="' . $block_width . 'mm" style="font-size: ' . $font_size . '; line-height: ' . $line_height . 'pt;">';
        $tail = '</table>';
        $row  = '<tr><td width="30mm" style="text-indent: 4pt;">%s</td><td width="38mm"><b style="font-family: latob;" style="text-indent: 4pt;">%s</b></td><td width="24mm" style="text-indent: 4pt;">%s</td></tr>';

        list($numbers1, $numbers2) = array_chunk($staff_members, $row_split);
        //list($numbers1, $numbers2) = array_chunk($staff_members, ceil(count($array) / 2));


        $html1 = $head;
        foreach ($numbers1 as $staff) {

            $first_name = $staff['first_name'];
            $last_name  = strtoupper($staff['last_name']);

            $tel = trim($staff['tel']);
            if ($tel == '') {
                $tel = '<i style="color: red;">TBC</i>';
            }

            $html1 .= sprintf($row, $first_name, $last_name, $tel);
        }
        $html1 .= $tail;

        #echo $html1;
        $pdf->writeHTMLCell(0, 0, '', $top, $html1, 0, 1, 0, true, '', true);


        $html2 = $head;
        foreach ($numbers2 as $staff) {

            $first_name = $staff['first_name'];
            $last_name  = strtoupper($staff['last_name']);

            $tel = trim($staff['tel']);
            if ($tel == '') {
                $tel = '<i style="color: red;">TBC</i>';
            }

            $html2 .= sprintf($row, $first_name, $last_name, $tel);
        }
        $html2 .= $tail;

        #echo $html2; exit;
        $left = $block_margin + $block_width + $block_gutter;
        #echo $left; exit;
        $pdf->writeHTMLCell(0, 0, $left, $top, $html2, 0, 1, 0, true, '', true);


        $html3 = $head;
        foreach ($useful_numbers as $name => $number) {

            $html3 .= '<tr><td width="68mm" style="text-indent: 4pt;"><b style="font-family: latob;">' . $name . '</b></td><td width="24mm" style="text-indent: 4pt;">' . $number . '</td></tr>';
        }
        $html3 .= $tail;

        // Calculate the height of the useful block:
        $n = count($useful_numbers);
        $r_height = $border_width + ($cell_padding * 2) + $line_height;
        $b_height = (($n * $r_height) + $border_width) * 0.352778;
        $b_top = $page_height - $b_height - $block_margin;

        #echo $html3;
        $pdf->writeHTMLCell(0, 0, $left, $b_top, $html3, 0, 1, 0, true, '', true);


        $html4 = '<h2 style="margin: 0; padding: 0; font-size: 16pt; line-height: ' . ($line_height / 2) . 'mm;">Useful numbers</h2>';

        $pdf->writeHTMLCell(0, 0, $left, ($b_top - 10), $html4, 0, 1, 0, true, '', true);


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