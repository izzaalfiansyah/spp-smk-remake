<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function base64Upload($req, $path = '/', $ext = '.txt')
    {
        $file = explode(';base64,', $req)[1];
        $filename = date('YmdHis') . $ext;

        if (file_put_contents(public_path($path . $filename), base64_decode($file))) {
            return $filename;
        }

        return false;
    }

    public function formatMoney($value)
    {
        $number = (int) $value;
        return 'Rp ' . number_format($number, 0, ',', '.');
    }

    private function generateTD($content)
    {
        $htmlTd = '';
        $tds = [];
        foreach ($content as $item) {
            if ($item == '') {
                if (count($tds) <= 0) {
                    array_push($tds, [
                        'td' => $item,
                        'colspan' => 1,
                    ]);
                } else {
                    $tds[count($tds) - 1]['colspan'] += 1;
                }
            } else {
                array_push($tds, [
                    'td' => $item,
                    'colspan' => 1,
                ]);
            }
        }

        foreach ($tds as $td) {
            $align = '';

            if ($td['colspan'] > 1) {
                $align = 'center';
            }

            $htmlTd .= '<td colspan="' . $td['colspan'] . '" align="' . $align . '">' . $td['td'] . '</td>';
        }

        return $htmlTd;
    }

    public function toPrint($contents, $headers = [], $footers = [], $title = '')
    {
        $header = "";
        $header = $this->generateTD($headers);

        if ($contents) {
            $body = "";
            foreach ($contents as $content) {
                $body .= "<tr>";
                $body .= $this->generateTD($content);
                $body .= "</tr>";
            }
        } else {
            $span = count($headers);
            $body = "
                <tr>
                    <td colspan=$span align=center>Data tidak tersedia.</td>
                </tr>
            ";
        }

        $footer = $this->generateTD($footers);

        $response = "
        <span style='font-size: 12px'>$title</span>
        <table border=1 cellpadding=1 cellspacing=0 width=100% style='font-size: 12px !important;'>
            <tr>
                $header
            </tr>
            $body
            <tr>
                $footer
            </tr>
        </table>
        <script>
            window.print();
            setTimeout(window.close, 3000);
        </script>
        ";

        return $response;
    }

    public function toExcel($contents, $headers = [], $footers = [], $filename)
    {
        $response = "";

        if ($headers) {
            $response .= implode("\t", $headers) . "\n";
        }

        if ($contents) {
            foreach ($contents as $content) {
                $response .= implode("\t", $content) . "\n";
            }
        } else {
            $response .= "Data tidak tersedia.\n";
        }

        if ($footers) {
            $response .= implode("\t", $footers) . "\n";
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=' . ($filename ?: date('YmdHis')) . '.xlsx');

        return $response;
    }
}
