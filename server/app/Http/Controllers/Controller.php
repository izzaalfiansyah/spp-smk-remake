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

    public function toPrint($contents, $headers = [], $footers = [], $title = '')
    {
        $header = "";
        foreach ($headers as $item) {
            $header .= "<td>{$item}</td>";
        }

        if ($contents) {
            $body = "";
            foreach ($contents as $content) {
                $body .= "<tr>";
                foreach ($content as $item) {
                    $body .= "<td>$item</td>";
                }
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

        $footer = "";
        foreach ($footers as $item) {
            $footer .= "<td>{$item}</td>";
        }

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
