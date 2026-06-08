<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QRCodeGenerator
{
    public static function generatePngBase64(string $url): string
    {
        $options = new QROptions([
            'version' => 5,
            'eccLevel' => EccLevel::L,
            'outputType' => QROutputInterface::OUTPUT_IMAGE_PNG,
            'scale' => 10,
        ]);

        return (new QRCode($options))->render($url);
    }

    public static function generatePdf(string $restaurantName, string $url)
    {
        $qrCodeBase64 = self::generatePngBase64($url);

        $html = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <title>Menu QR Code</title>
            <style>
                body {
                    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
                    text-align: center;
                    padding: 50px;
                    color: #1a3c2f;
                    background-color: #ffffff;
                }
                .card {
                    border: 3px solid #c8a97e;
                    border-radius: 20px;
                    padding: 40px;
                    display: inline-block;
                    background-color: #f5f0e8;
                }
                h1 {
                    font-size: 32px;
                    margin-bottom: 5px;
                    color: #1a3c2f;
                }
                p {
                    font-size: 18px;
                    color: #7a8a7e;
                    margin-bottom: 30px;
                }
                .qr-img {
                    width: 260px;
                    height: 260px;
                    border: 8px solid #ffffff;
                    border-radius: 12px;
                }
                .footer {
                    margin-top: 40px;
                    font-size: 12px;
                    color: #7a8a7e;
                }
            </style>
        </head>
        <body>
            <div class='card'>
                <h1>{$restaurantName}</h1>
                <p>Scan to view our digital menu</p>
                <img class='qr-img' src='{$qrCodeBase64}' />
                <div class='footer'>Powered by Digital Menu Platform</div>
            </div>
        </body>
        </html>
        ";

        return Pdf::loadHTML($html)->setPaper('a4', 'portrait')->output();
    }
}
