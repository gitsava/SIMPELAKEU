<template>
  <div class="">
    <div class="row">
        <div class="col-xs-12 col-md-3">
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon">Proyek</span>
                    <v-select v-model="searchKategori" :options="kategoriOptions" :settings="kategoriSetting" @search:focus="maybeLoadKategori"/>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-md-4">
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
                    <th>Total</th>
                    <th>Proyek</th>
                    <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="!editClicked">
                        <td>03/19/2018</td>
                        <td>Raihan</td>
                        <td>Pembelian ATK</td>
                        <td>100 000 000</td>
                        <td>Gifu University</td>
                        <td>
                            <button type="button" id="edit" class="btn btn-box-tool" v-on:click="edit(editClicked)"><i class="fa fa-check"></i></button>
                            <button type="button" class="btn btn-box-tool"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td>03/19/2018</td>
                        <td>Raihan</td>
                        <td>Pembelian ATK</td>
                        <td>100 000 000</td>
                        <td>Gifu University</td>
                        <td>
                            <button type="button" class="btn btn-box-tool"><i class="fa fa-check"></i></button>
                            <button type="button" class="btn btn-box-tool"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td>03/19/2018</td>
                        <td>Raihan</td>
                        <td>Pembelian ATK</td>
                        <td>100 000 000</td>
                        <td>Gifu University</td>
                        <td>
                            <button type="button" class="btn btn-box-tool"><i class="fa fa-check"></i></button>
                            <button type="button" class="btn btn-box-tool"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="clearfix">
        <ul class="pagination pagination-sm no-margin">
            <li><a href="#">«</a></li>
            <li><a href="#">1</a></li>
            <li><a href="#">2</a></li>
            <li><a href="#">3</a></li>
            <li><a href="#">»</a></li>
        </ul>
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
            }
        },
        data: () => ({
            isloading: false,
            success : 'success',
            editClicked : false,
            form2: new Form({
                idKategori: 0,
                idPegawai: '',
                tanggal: '',
                nominalDebit: '0',
                nominalKredit: '100 000',
                keterangan: ''
            }),
            kategoriOptions: [],
            picOptions: [],
            kategoriSetting: {
                width:'100%'
            },
            picSetting: {
                width:'100%',
            },
            searchKategori:''
        }),
        methods: {
            loadData: function(loadStatus){
                console.log(loadStatus)
                this.isloading = !loadStatus
            },
            edit: function(i){
                this.$parent.$parent.editModalShow()
            },
            maybeLoadPIC() {
                return this.picOptions.length <= 0 ? this.getAllPegawaiList() : null
            },
            maybeLoadKategori() {
                return this.kategoriOptions.length <= 0 ? this.getAllKategoriList() : null
            },
            getAllPegawaiList(){
                let url = '/api/pegawai/getallpegawailist'
                let self = this
                fetch(url)
                    .then(res => res.json())
                    .then(res => {
                    let data = res.data;
                    for(var i = 0; i < data.length; i++){
                        self.picOptions.push({
                            label : data[i].nama,
                            value : data[i].id
                        })
                    }
                    })
                    .catch(err => console.log(err));
            },
            getAllKategoriList(){
                let url = '/api/transaksiumum/getallkategorilist'
                let self = this
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
                    })
                    .catch(err => console.log(err));
            }
        }
    }
</script>