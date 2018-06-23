<template>
    <div class="col-md-12">
        <div class="box box-success collapsed-box">
            <div class="box-header with-border" data-widget="collapse" style="cursor:pointer">
                <h3 class="box-title">Tambah Transaksi Umum</h3>
                <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool"><i class="fa fa-plus"></i>
                </button>
                </div>
            </div>
            <!-- /.box-header -->
            <form @submit.prevent="true" role="form">
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-12 col-md-4">
                            <div class="form-group">
                                <label>Kategori</label>
                                <v-select ref="select" v-model="form.idKategori" :options="kategoriOptions" :settings="kategoriSetting" @search:focus="maybeLoadKategori"/>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-4">
                            <div class="form-group">
                                <label>PIC</label>
                                <v-select v-model="form.idPegawai" :options="picOptions" :settings="picSetting" @search:focus="maybeLoadPIC"/>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-4">
                            <div class="form-group">
                                <label>Tanggal</label>
                                <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input v-model="form.tanggal" type="text" class="form-control pull-right" id="datepicker">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-3">
                            <div class="form-group">
                                <label>Tipe Nominal</label>
                                <select class="form-control" v-model="form.tipeNominal">
                                    <option value="1">Debit</option>
                                    <option value="2">Kredit</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-9">
                            <div class="form-group">
                                <label>Nominal</label>
                                <div class="input-group">
                                    <span class="input-group-addon">Rp</span>
                                    <input v-model="form.nominal" type="text" class="form-control">
                                    <span class="input-group-addon">,00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea v-model="form.keterangan" class="form-control" rows="3" placeholder="Isikan Keterangan ..."></textarea>
                    </div> 
                    <div class="form-group">
                        <label v-if="form.tipeNominal == 1">Simpan ke Bank</label>
                        <label v-if="form.tipeNominal == 2">Ambil dari Bank</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                            <input type="checkbox" v-model="bankChecked">
                            </span>
                            <select v-model="form.idBank" class="form-control" :disabled="!bankChecked">
                                    <option value="1">Bank A</option>
                                    <option value="2">Bank B</option>
                            </select>
                        </div>
                    </div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <v-button :type="primary" :loading="form.busy">Simpan</v-button>
                </div>
                <!-- /.table-responsive -->
            </form>
            <!-- /.box-body -->
        </div>
    </div>
    <!-- /.box -->
</template>
<script>
    import Form from 'vform'
    export default {
        data:()=>({
            form: new Form({
                idKategori: 0,
                idPegawai: '',
                tanggal: '',
                tipeNominal: 1,
                nominal: 0,
                keterangan: '',
                idBank: 1
            }),
            kategoriOptions: [],
            picOptions: [],
            kategoriSetting: {
                width:'100%',
                placeholder:'Cari Kategori ...'
            },
            picSetting: {
                width:'100%',
                placeholder:'Cari Nama PIC ...'
            },
            primary: 'primary',
            bankChecked : false
        }),

        created(){
        },
        methods:{
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
    $(function() {
        $('#datepicker').datepicker({
            autoclose: true
        })
    });
</script>