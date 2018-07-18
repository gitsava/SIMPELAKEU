<template>
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Riwayat Transaksi Umum</h3>
                <button type="button" class="btn btn-primary pull-right" :class="{disabled: list.length == 0}" @click="downloadExcel">Download Excel</button>
            </div>
            <div v-if="isloading" class="overlay">
                <i class="fa fa-refresh fa-spin"></i>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12 col-md-5">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon">Kategori</span>
                                <v-select ref="select" v-model="kategori" :options="kategoriOptions" :settings="kategoriSetting" @search:focus="maybeLoadKategori"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-2">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon">Tahun</i></span>
                                <input type="text" class="form-control" v-model="tahun">
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-1">
                        <div class="form-group">
                            <button type="button" class="btn btn-primary" @click="loadData">Filter</button>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-3">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                <input type="text" class="form-control" placeholder="Cari disini...">
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-1">
                        <div class="form-group">
                            <v-button :type="success" :loading="isloading" :method="loadData" :block="true">Cari</v-button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-md-12">
                        <div class="table-responsive">
                            <table class="table no-margin">
                                <thead>
                                <tr>
                                <th>Tanggal</th>
                                <th>Pemohon</th>
                                <th>Keterangan</th>
                                <th>Debit</th>
                                <th>Kredit</th>
                                <th>Saldo</th>
                                <th>Kategori</th>
                                <th>Aksi</th>
                                </tr>
                                </thead>
                                <tbody>
                                <template v-if="!empty" v-for="(item, i) in indexList[currentPage]">
                                        <tr>
                                            <td>{{ list[item].tanggal }}</td>
                                            <td>{{ list[item].pegawai }}</td>
                                            <td class="keterangan">{{ list[item].keterangan }}</td>
                                            <template v-if="list[item].nominal_type == 'd'">
                                                <td>{{ list[item].nominal | currency}}</td>
                                                <td ></td>
                                            </template>
                                            <template v-if="list[item].nominal_type == 'k'">
                                                <td></td>
                                                <td >{{ list[item].nominal | currency}}</td>
                                            </template>
                                            <template v-if="list[item].nominal_type == ''">
                                                <td></td>
                                                <td></td>
                                            </template>
                                            <td v-if="list[item].saldo >= 0" class="pull-right">{{ list[item].saldo | currency}}</td>
											<td v-if="list[item].saldo < 0" class="pull-right">({{ -list[item].saldo | currency}})</td>
                                            <td  v-if="list[item].kategori.length > 17" data-toggle="tooltip" :title="list[item].kategori">{{ list[item].kategori.substring(0,17) }}...</td>
                                            <td  v-if="list[item].kategori.length <= 17">{{ list[item].kategori }}</td>
                                            <td style="width:100px">
                                                <button v-if="list[item].edit_able" type="button" id="edit" class="btn btn-box-tool" v-on:click="edit(item)"><i class="fa fa-edit"></i></button>
                                                <button v-if="list[item].delete_able" type="button" class="btn btn-box-tool" v-on:click="deleteAlert(item)"><i class="fa fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            <p v-if="empty" style="text-align:center"> No Records Found. </p>
                        </div>
                    </div>
                </div>
                <!-- /.table-responsive -->
            </div>
            <!-- /.box-body -->
            <div class="box-footer clearfix">
                <ul class="pagination pagination-sm no-margin">
                    <li :class="{'disabled':currentPage == 0}" style="cursor:pointer" @click="changePage(currentPage-1)"><a>«</a></li>
                    <template v-for="n in page">
                        <li :class="{'disabled':currentPage == n-1}" style="cursor:pointer" @click="changePage(n-1)"><a>{{ n }}</a></li>
                    </template>
                    <li :class="{'disabled':currentPage == page-1}" style="cursor:pointer" @click="changePage(currentPage-1)"><a>»</a></li>
                </ul>
            </div>
        </div>
    </div>
</template>
<script>
    import Form from 'vform'
    export default {
        layout: 'default',
        props: {
            hidden:{
                type: Boolean,
                default: true
            },
            isloading:{
                type: Boolean,
                default: false
            },
            list:{},
            empty : {
                type : Boolean,
                default : true
            },
            page:{
                type: Number,
                default: 1
            },
            currentPage:{
                type: Number,
                default: 1
            },
            indexList: {}
        },
        data: () => ({
            success : 'success',
            kategori: null,
            kategoriOptions: [],
            kategoriSetting: {
                width:'100%'
            },
            date : new Date(),
            tahun: null,
            filteredData : [],
            search: null,
        }),
        methods: {
            loadData: function(){
                this.tahun = this.date.getFullYear()
                this.$parent.getTransaksiUmum(this.kategori['value'],this.tahun)
            },
            edit: function(i){
                this.$parent.editModalShow(i)
                this.$parent.selectedKategori = this.kategori['value']
            },
            deleteAlert : function(i){
                this.$parent.deleteAlertShow(i)
                this.$parent.selectedKategori = this.kategori['value']
            },
            changePage(toPage){
                this.$parent.currentPage = toPage
            },
            submitSearch(){
                this.$parent.submitSearch(this.search)
                console.log(this.list)
            },
            downloadExcel(){
                var url = "/api/downloadexcel/laporantahunanumum?tahun="+this.tahun+"&idKategori="+this.kategori['value']
                let urlGenerate = '/api/generateexcel/laporantahunanumum?tahun='+this.tahun+"&idKategori="+this.kategori['value']
                this.$parent.showGenerate()
                fetch(urlGenerate)
                  .then(res => res.json())
                  .then(res => {
                    if(res.status == true){
                      var result = document.createElement('a'); 
                      result.href = url;
                      result.download = 'Laporan Keuangan '+this.kategori['label']+' '+this.tahun+'.xlsx';
                      result.click();
                      this.$parent.hideGenerate()
                    }
                  })
                  .catch(err => console.log(err));
            },
            maybeLoadKategori() {
                return this.kategoriOptions.length <= 0 ? this.getAllKategoriList() : null
            },
            getAllKategoriList(){
                let url = '/api/transaksiumum/getallkategorilist'
                let self = this
                this.$refs.select.toggleLoading(true)
                fetch(url)
                    .then(res => res.json())
                    .then(res => {
                        let data = res.data;
                        for(var i = 0; i < data.length; i++){
                            self.kategoriOptions.push({
                                label : data[i].nama_kategori,
                                value : data[i].id
                            })
                        }
                        this.$refs.select.toggleLoading(false)
                    })
                    .catch(err => console.log(err));
            }
        }
    }
</script>
<style>

td.keterangan{
    width: 300px;
}    
</style>