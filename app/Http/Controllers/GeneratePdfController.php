<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Fpdf;


class GeneratePdfController extends Controller
{
    //
    public function headerform($id){
        $get_form=Pengadaan::where('id',$id)->first();
        $get_tanggal=date('j F Y', strtotime($get_form->tanggal_pengajuan));
        $pdf = new Fpdf();
        $pdf::SetTitle('Pengajuan ItemPengadaan sarpras tanggal'.' '.$get_tanggal);
        /*HEADER PDF*/
        $pdf::SetLineWidth(0.5);
        $pdf::SetFont('Arial','',10);
        $pdf::Cell(40,40,$pdf::Image(public_path().'/images/logoipb.png',15,15,30),1,'C');
        $pdf::Cell(70,30,'',1,'C');
        $pdf::SetFont('Arial','',12);
        $pdf::Cell(40,10,'Nomor Dokumen',1,'C');
        $pdf::SetFont('Arial','',10);
        $pdf::Cell(45,10,': F-RM/ADM-06/02/00',1,'C');
        $pdf::Ln();
        $pdf::SetFont('Arial','',12);    
        $pdf::Cell(110);
        $pdf::Cell(40,10,'Edisi/Revisi',1,'C');
        $pdf::Cell(45,10,': 2/0',1,'C');
        $pdf::Ln();
        $pdf::SetFont('Arial','B',12);    
        $pdf::Cell(40);
        $pdf::Cell(70,10,'',1,'C','B');
        $pdf::SetFont('Arial','',12);
        $pdf::Cell(40,10,'Tanggal',1,'C');
        $pdf::SetFont('Arial','',12);
        $pdf::Cell(45,10,': 3 Oktober 2016',1,'C');
        $pdf::Ln();
        $pdf::SetFont('Arial','',12);
        $pdf::Cell(40);
        $pdf::Cell(70,10,'',1,'C');
        $pdf::Cell(40,10,'Halaman',1,'C');
        $pdf::Cell(45,10,': 6',1,'C');
        $pdf::SetXY(50,10);
        $pdf::SetFont('Arial','',9);
        $pdf::Cell(70,10,'PUSAT STUDI BIOFARMAKA TROPIKA ',0,1,'C');
        $pdf::SetXY(50,20);
        $pdf::Cell(70,5,'LPPM-IPB',0,1,'C');
        $pdf::SetXY(50,30);
        $pdf::SetFont('Arial','B',12);    
        $pdf::Cell(70,10,'FORMULIR',0,1,'C');
        $pdf::SetXY(50,40);
        $pdf::SetFont('Arial','',10);
        $pdf::Cell(70,10,'PENGAJUAN DANA',0,1,'C');
       
    }

    public function footerform($id){
        $get_form=Pengadaan::where('id',$id)->first();
        $get_tanggal=date('j F Y', strtotime($get_form->tanggal_pengajuan));
        $pdf = new Fpdf();
        /*tanda tangan*/
        $pdf::Ln();
        $pdf::SetFont('Arial',null, 11);
        $pdf::Ln();
        $pdf::cell(17);
        $pdf::Cell(62, 6, "Pemohon,", 0, 0, 'L', 0);
        $pdf::Cell(65, 6, "Mengetahui,", 0, 0, 'L', 0);
        $pdf::Cell(65, 6, "Menyetujui,", 0, 0, 'L', 0);
        $pdf::ln();
        $pdf::cell(18);
        $pdf::Cell(42, 6, "Airlangga Peminjam", 0, 0, 'L', 0);
        $pdf::Cell(68, 6, "PJ Bagian Fasilitas & Properti", 0, 0, 'L', 0);
        $pdf::Cell(100, 6, "Kepala Pusat Studi Biofarmaka", 0, 0, 'L', 0);
        $pdf::ln();
        $pdf::cell(19);
        $pdf::Cell(48, 6, "       ", 0, 0, 'L', 0);
        $pdf::Cell(85, 6, "                   ", 0, 0, 'L', 0);
        $pdf::Cell(100, 6, "Tropika", 0, 0, 'L', 0);
        $pdf::Ln();
        $pdf::Ln();
        $pdf::Ln();
        $pdf::Ln();
        $pdf::Ln();
        $pdf::cell(5);
        $pdf::Cell(65, 6, '( ...................................... ) ', 0, 0, 'L', 0);
        $pdf::Cell(65, 6, '( ...................................... ) ', 0, 0, 'L', 0);
        $pdf::Cell(65, 6, '( ...................................... ) ', 0, 0, 'L', 0);
        /*FOOTER*/
        $pdf::SetLineWidth(0.7);
        $pdf::Line(20,246,190,246);
        $pdf::SetLineWidth(0.1);
        $pdf::Line(20,247,190,247);
     
        $pdf::SetFont('Arial', '', 11);
        $pdf::Ln();
        $pdf::Ln();
        
        $pdf::SetFont('Arial', 'B', 12);
        $pdf::cell(190,5,'PUSAT STUDI BIOFARMAKA TROPIKA LPPM-IPB',0,1,'C');
        $pdf::SetFont('Arial', '', 12);
        $pdf::cell(190,5,'Kampus IPB Taman Kencana, JL. Taman Kencana N0.3, Bogor 16128',0,1,'C');
        $pdf::cell(190,5,'Telp/Faks: 0251-8373561/0251-8347525; Email: bfarmaka@gmail.com; Website: ',0,1,'C');
        $pdf::cell(190,5,'http://biofarmaka.ipb.ac.id',0,1,'C');
    }

    public function headertable($transaksiProyek,$transaksi){
            setlocale(LC_TIME,'id_IN');
            $pdf = new Fpdf();
            $pdf::SetLineWidth(0.1);
            $pdf::SetFont('Arial',null, 11);
            $pdf::Ln(5);
            $pdf::cell(8);
            //atribut ke 4 buat pake garis/border 0 tidak 1 ya
            $pdf::Cell(176, 30, "", 0, 0, 'L', 0);
            $pdf::ln(5);
            $pdf::SetXY(10,60);
            $pdf::cell(8);
            $pdf::Cell(162, 8, "Nama Kegiatan          :".' '.$transaksiProyek->proyek->nama_kegiatan, 0, 0, 'L', 0);
            $pdf::ln(5);
            $pdf::cell(8);
            $pdf::Cell(162, 8, "Hari/Tanggal Kegiatan  :".' '.strftime('%A',strtotime($transaksiProyek->proyek->tanggal_awal))
                       .'/'.date('d-m-Y', strtotime($transaksiProyek->proyek->tanggal_awal)).'-'.strftime('%A',strtotime($transaksiProyek->proyek->tanggal_akhir))
                       .'/'.date('d-m-Y', strtotime($transaksiProyek->proyek->tanggal_akhir)), 0, 0, 'L', 0);
            $pdf::ln(5);
            $pdf::cell(8);
            $pdf::Cell(162, 8, "Waktu                  :  ", 0, 0, 'L', 0);
            $pdf::ln(5);
            $pdf::cell(8);
            $pdf::Cell(162, 8, "Tempat                 :  ", 0, 0, 'L', 0);
            $pdf::ln(5);
            $pdf::cell(8);
            $pdf::Cell(162, 8, "Tanggal Pengajuan      :  ".date('d-m-Y', strtotime($transaksi->tanggal)), 0, 0, 'L', 0);
            $pdf::ln(4);
            $pdf::cell(8);
            /*HEADER TABEL*/
            $pdf::SetFont('Arial','B', 10);
            $pdf::Ln(5);
            $pdf::SetFillColor(204 , 204, 204);
            $pdf::cell(8);
            $pdf::Cell(10, 10, "NO", 1, 0, 'C', 1);
            $pdf::SetXY(28,89);
            $pdf::Cell(43, 10, 'KETERANGAN', 1, 0, 'C', 1);
            $pdf::Cell(23, 10, 'JMLH', 1, 0, 'C', 1);
            $pdf::Cell(18, 10, 'UNIT', 1, 0, 'C', 1);
            $pdf::Cell(38, 10, 'PERKIRAAN (Rp)', 1, 0, 'C', 1);
            $pdf::Cell(31, 10, 'SUB TOTAL (Rp)', 1, 0, 'C', 1);
            //set font untuk data
            $pdf::SetFont('Arial',null, 8);
    }    
}
