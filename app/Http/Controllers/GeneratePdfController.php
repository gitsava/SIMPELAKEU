<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Dompdf\Dompdf;
use App\Transaksi;


class GeneratePdfController extends Controller
{
    //
    private $header = '';
    private $body = '';
    private $footer = '';
    private $sign = '';

    public function headerform(){
       $this->header = '<div style="position: fixed; top: -40px; left: 0px; right: 0px; height: 200px">
                        <table style="height: 100px;" border="1" cellspacing="4" cellpadding="1">
                        <tbody>
                        <tr style="height: 2px;">
                        <td style="height: 10.7327px; width: 80px;" rowspan="4"><img height="80" width="80" src="storage/ipb.png" /></td>
                        <td style="text-align: center; height: 6px; width: 300px;" rowspan="2">PUSAT STUDI BIOFARMAKA TROPIKA LPPM-IPB</td>
                        <td style="height: 2px; width: 139px;">Nomor Dokumen</td>
                        <td style="height: 2px; width: 171px;">: F-RM/ADM-06/02/00</td>
                        </tr>
                        <tr style="height: 4px;">
                        <td style="height: 4px; width: 139px;">Edisi/Revisi</td>
                        <td style="height: 4px; width: 171px;">: 2/0</td>
                        </tr>
                        <tr style="height: 3px;">
                        <td style="height: 3px; width: 216.097px; text-align: center;"><strong>FORMULIR</strong></td>
                        <td style="height: 3px; width: 139px;">Tanggal</td>
                        <td style="height: 3px; width: 171px;">: 3 Oktober 2016</td>
                        </tr>
                        <tr style="height: 1px;">
                        <td style="height: 1px; width: 216.097px; text-align: center;">PENGAJUAN DANA</td>
                        <td style="height: 1px; width: 139px;">Halaman</td>
                        <td style="height: 1px; width: 171px;">: 6</td>
                        </tr>
                        </tbody>
                        </table>
                        </div>';
        return $this->header;
    }

    public function footerform(){
        $this->footer = '<div style="position: fixed; bottom: 0px; left: 0px; right: 0px; height: 100px">
                        <p>&nbsp;</p>
                        <hr style="display: block; height: 1px; border: 0; 
                            border-top: 4px solid #800000; border-bottom: 1px solid #800000; margin: 1em 0; padding: 0;" />
                        <p style="text-align: center;"><strong>PUSAT STUDI BIOFARMAKA TROPIKA LPPM-IPB</strong></p>
                        <p style="text-align: center;">Kampus IPB Taman Kencana, Jl. Taman Kencana No. 3, Bogor 16128 <br /> 
                            Telp/Faks: 0251-8373561/0251-8347525; Email: bfarmaka@gmail.com; Website: http://biofarmaka.ipb.ac.id</p>
                        </div>';
        return $this->footer;
    }

    public function signform(){
        $this->sign = '<table style="margin-top: 10px;">
                        <tbody>
                        <tr>
                        <td style="text-align: center;" width="170">Pemohon,</td>
                        <td style="text-align: center;" width="170">Mengetahui,</td>
                        <td style="text-align: center;" width="170">Menyetujui,</td>
                        </tr>
                        <tr>
                        <td width="170">
                        <p>&nbsp;</p>
                        </td>
                        <td width="170">
                        <p>&nbsp;</p>
                        </td>
                        <td width="170">
                        <p>&nbsp;</p>
                        </td>
                        </tr>
                        <tr>
                        <td width="170">&nbsp;</td>
                        <td width="170">&nbsp;</td>
                        <td width="170">&nbsp;</td>
                        </tr>
                        <tr>
                        <td width="170">&nbsp;</td>
                        <td width="170">&nbsp;</td>
                        <td width="170">&nbsp;</td>
                        </tr>
                        <tr>
                        <td style="text-align: center;" width="170">( &hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;)</td>
                        <td style="text-align: center;" width="170">(&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;)</td>
                        <td style="text-align: center;" width="170">( &hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;)</td>
                        </tr>
                        </tbody>
                        </table>';
        return $this->sign;
    }

    public function bodyform($transaksiAll){
        $tableheader = '<table style="margin-left: 10px; margin-top: 90px; border: 1px solid black;" width="540">
                        <tbody>
                        <tr style="height: 12px;">  
                        <td style="height: 12px;" width="173">Nama Kegiatan</td>
                        <td style="font-size: 12px; height: 12px;" width="17">:</td>
                        <td style="height: 12px;" width="200">'.$transaksiAll[0]->transaksiProyek[0]->proyek->nama_kegiatan.'</td>
                        </tr>
                        <tr style="height: 15px;">
                        <td style="height: 15px;" width="173">Hari/Tanggal Kegiatan</td>
                        <td style="height: 15px;" width="17">:</td>
                        <td style="height: 15px;" width="200">&nbsp;</td>
                        </tr>
                        <tr style="height: 14px;">
                        <td style="height: 14px;" width="173">Waktu</td>
                        <td style="height: 14px;" width="17">:</td>
                        <td style="height: 14px;" width="200">&nbsp;</td>
                        </tr>
                        <tr style="height: 17px;">
                        <td style="height: 17px;" width="173">Tempat</td>
                        <td style="height: 17px;" width="17">:</td>
                        <td style="height: 17px;" width="200">&nbsp;</td>
                        </tr>
                        <tr style="height: 24px;">
                        <td style="height: 24px;" width="173">Tanggal Pengajuan</td>
                        <td style="height: 24px;" width="17">:</td>
                        <td style="height: 24px;" width="200">'.$transaksiAll[0]->tanggal.'</td>
                        </tr>
                        </tbody>
                        </table>';
        $itemHeader = '<table style="border-collapse: collapse; margin-top: 10px;" border="1">
                        <tbody>
                        <tr style="height: 45px;">
                        <td style="background-color: #c0c0c0; text-align: center; height: 45px;" width="30"><strong>NO</strong></td>
                        <td style="background-color: #c0c0c0; text-align: center; height: 45px;" width="200"><strong>KETERANGAN</strong></td>
                        <td style="background-color: #c0c0c0; text-align: center; height: 45px;" width="40"><strong>JMLH</strong></td>
                        <td style="background-color: #c0c0c0; text-align: center; height: 45px;" width="60"><strong>UNIT</strong></td>
                        <td style="background-color: #c0c0c0; text-align: center; height: 45px;" width="100"><strong>PERKIRAAN BIAYA</strong></td>
                        <td style="background-color: #c0c0c0; text-align: center; height: 45px;" width="100"><strong>SUB TOTAL</strong></td>
                        </tr>';
        $itemBody = '';
        $no = 1;
        $total = 0;
        foreach($transaksiAll as $transaksi){
            $subtotal = $transaksi->transaksiProyek[0]->jumlah == 0 || $transaksi->transaksiProyek[0]->jumlah == null ? 
                        $transaksi->transaksiProyek[0]->perkiraan_biaya : 
                        ($transaksi->transaksiProyek[0]->jumlah * $transaksi->transaksiProyek[0]->perkiraan_biaya);
            $itemBody = $itemBody.'<tr style="height: 30px;">
                                    <td style="height: 35px;" width="30">'.$no.'</td>
                                    <td style="height: 35px;" width="200">'.$transaksi->transaksiProyek[0]->keterangan.'</td>
                                    <td style="height: 35px;" width="30">'.$transaksi->transaksiProyek[0]->jumlah.'</td>
                                    <td style="height: 35px;" width="60">'.$transaksi->transaksiProyek[0]->unit.'</td>
                                    <td style="height: 35px;" width="100">'.$transaksi->transaksiProyek[0]->perkiraan_biaya.'</td>
                                    <td style="height: 35px;" width="100">'.$subtotal.'</td>
                                    </tr>';
            $no++;
            $total = $total + $subtotal;
        }
        if(($no-1)<10){
            for($i=$no; $i<=10; $i++){
                $itemBody = $itemBody.'<tr style="height: 35px;">
                                        <td style="height: 35px;" width="30">&nbsp;</td>
                                        <td style="height: 35px;" width="200">&nbsp;</td>
                                        <td style="height: 35px;" width="30">&nbsp;</td>
                                        <td style="height: 35px;" width="60">&nbsp;</td>
                                        <td style="height: 35px;" width="100">&nbsp;</td>
                                        <td style="height: 35px;" width="100">&nbsp;</td>
                                        </tr>';
            }
        }
        $itemFooter = '<tr style="height: 35px;">
                        <td style="height: 35px;" width="30">&nbsp;</td>
                        <td style="height: 35px;" colspan="4" width="200"><strong>&nbsp;TOTAL</strong></td>
                        <td style="height: 35px;" width="100">'.$total.'</td>
                        </tr>
                        </tbody></table>';
        $this->body = $tableheader.$itemHeader.$itemBody.$itemFooter;
        return $this->body;
    }

    public function generate($transaksiAll){
        $html = $this->headerform().$this->bodyform($transaksiAll).$this->signform().$this->footerform();
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();
        return $dompdf->stream();
    }
}
