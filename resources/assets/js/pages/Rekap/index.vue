<template>
    <section class="content">
        <div class="modal fade" id="modal-generate">
            <div class="modal-dialog middle" >
                <div class="modal-content">
                    <div class="modal-header">
                    <h4 class="modal-title" style="text-align:center">Generating Excel File</h4>
                    </div>
                    <div class="modal-body">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%">
                            Please Wait...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div v-if="loadingDownload" class="overlay">
                        <i class="fa fa-refresh fa-spin"></i>
                    </div>
                    <div class="box-header with-border">
                        <h3 class="box-title">Download Rekap Laporan Keuangan</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-xs-6 col-md-2">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon">Tahun</i></span>
                                        <input type="text" v-model="tahunDownload" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-4 col-md-1">
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary" @click="downloadExcel">Download</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Rekap Laporan Keuangan Perbulan</h3>
                    </div>
                    <!-- /.box-header -->
                    <div v-if="isloading" class="overlay">
                        <i class="fa fa-refresh fa-spin"></i>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-xs-12 col-md-4">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon">Bulan</i></span>
                                        <select class="form-control" v-model="bulan">
                                            <template v-for="(item, i) in months">
                                                <option :value="i+1">{{ item }}</option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon">Tahun</i></span>
                                        <input type="text" v-model="tahun" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-1">
                                <div class="form-group">
                                    <button type="button" class="btn btn-primary" @click="loadData">Filter</button>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-12">
                                <div class="table-responsive">
                                    <table class="table no-margin">
                                        <thead>
                                        <tr>
                                        <th style="text-align:center">No.</th>
                                        <th style="text-align:center">POS ANGGARAN/KEGIATAN</th>
                                        <th style="text-align:center">{{ months[bulan-1] }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <template v-if="!emptyData" v-for="(item,i) in data">
                                                <tr>
                                                    <td style="width:50px">{{ i+1 }}</td>
                                                    <td >{{ item.kegiatan }}</td>
                                                    <td v-if="item.saldo>=0" class="pull-right">{{ item.saldo | currency}}</td>
													<td v-if="item.saldo<0" class="pull-right">({{ -item.saldo | currency}})</td>
                                                </tr>
                                        </template>
                                        <template v-if="!emptyData">
                                            <tr>
                                                <td></td>
                                                <td style="text-align:center">Total</td>
                                                <td v-if="totalSaldoData>=0" class="pull-right">{{ totalSaldoData | currency}}</td>
												<td v-if="totalSaldoData<0" class="pull-right">({{ -totalSaldoData | currency}})</td>
                                            </tr>
                                        </template>
                                        </tbody>
                                    </table>
                                    <p v-if="emptyData" style="text-align:center"> No Records Found. </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer clearfix">
                        <div class="row">
                            <div class="col-xs-12 col-md-12">
                                <div class="table-responsive">
                                    <table class="table no-margin">
                                        <tbody>
                                        <template v-if="!emptySimpanan" v-for="(item, i) in dataSimpanan">
                                                <tr>
                                                    <td style="width:50px">{{ i+1 }}</td>
                                                    <td >{{ item.kegiatan }}</td>
                                                    <td v-if="item.saldo>=0" class="pull-right">{{ item.saldo | currency}}</td>
													<td v-if="item.saldo<0" class="pull-right">({{ -item.saldo | currency}})</td>
                                                </tr>
                                        </template>
                                        <template v-if="!emptySimpanan">
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td style="text-align:center">Total</td>
                                                <td v-if="totalSaldoSimpanan>=0" class="pull-right">{{ totalSaldoSimpanan | currency}}</td>
												<td v-if="totalSaldoSimpanan<0" class="pull-right">({{ -totalSaldoSimpanan | currency}})</td>
                                            </tr>
                                        </template>
                                        </tbody>
                                    </table>
                                    <p v-if="emptySimpanan" style="text-align:center"> No Records Found. </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="emptySimpanan || emptyData" style="margin-bottom:200px"></div>
    </section>
</template>

<script>
    import Cookies from 'js-cookie'
    export default {
        layout: 'default',
        props: {
        },
        data: () => ({
            success : 'success',
            data: [],
            dataSimpanan: [],
            isloading: false,
            loadingDownload: false,
            emptyData: true,
            emptySimpanan: true,
            bulan: null,
            tahun: null,
            tahunDownload: null,
            totalSaldoData: 0,
            totalSaldoSimpanan: 0,
            months: ['Januari','Februari','Maret','April','Mei','Juni'
                    ,'Juli','Agustus','September','November','Desember']
        }),
        created(){
            Cookies.set('p', 7, { expires: null })
        },
        methods: {
           loadData: function(){
               this.getAllRekap()
           },
           getAllRekap(){
                this.isloading = true
                let url = '/api/transaksi/rekap/fetch/'+this.bulan+'/'+this.tahun
                this.totalSaldoData= 0
                this.totalSaldoSimpanan= 0
                fetch(url)
                  .then(res => res.json())
                  .then(res => {
                    console.log(res)
                    this.emptyData = res.emptyData
                    this.emptySimpanan = res.emptySimpanan
                    if(this.emptyData){
                        this.data = []
                    }else{
                        this.data = res.data;
                        for(var i=0; i< this.data.length; i++){
                            if(this.data[i].saldo != '-')
                                this.totalSaldoData += this.data[i].saldo
                        }
                    }
                    if(this.emptySimpanan){
                        this.dataSimpanan = []
                    }else{
                        this.dataSimpanan = res.dataSimpanan
                        for(var i=0; i< this.dataSimpanan.length; i++){
                            if(this.dataSimpanan[i].saldo != '-')
                                this.totalSaldoSimpanan += this.dataSimpanan[i].saldo
                        }
                    }
                    this.isloading = false
                  })
                  .catch(err => console.log(err));
            },
            downloadExcel(){
                var urlGenerate = "/api/transaksi/rekap/generaterekap/"+this.tahunDownload
                var url = "/api/transaksi/rekap/fetchrekap/"+this.tahunDownload
                $("#modal-generate").modal({ keyboard: false, backdrop: 'static' })
                $("#modal-generate").modal('show')
                fetch(urlGenerate)
                  .then(res => res.json())
                  .then(res => {
                    if(res.status == true){
                      var result = document.createElement('a'); 
                      result.href = url;
                      result.download = 'REKAP LAPORAN KEUANGAN JANUARI-DESEMBER '+this.tahunDownload+'.xlsx';
                      result.click();
                      $("#modal-generate").modal('hide')
                    }
                  })
                  .catch(err => console.log(err));
            },
        }
    }
</script>
<style>
  .middle{
    margin: 11% 25%;
  }  
</style>