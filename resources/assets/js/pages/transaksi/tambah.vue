<template>
    <div class="col-md-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Tambah Transaksi</h3>
            </div>
            <!-- /.box-header -->
            <form @submit.prevent="storeTransaksi" role="form">
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-12 col-md-6">
                            <div class="form-group">
                                <label>Jenis Transaksi</label>
                                <select class="form-control" v-model="jenisTransaksi" @change="changeJenisTransaksi()">
                                    <option value="1">Transaksi Umum</option>
                                    <option value="2">Transaksi Bank</option>
                                    <option value="3">Transaksi Proyek</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-6">
                            <div class="form-group">
                                <label>Kategori</label>
                                <v-select ref="select2" :disabled="kategoriDisabled" v-model="idKat" :onChange="changeSelectedKategori()" :options="kategoriOptions" :settings="kategoriSetting" @search:focus="maybeLoadKategori"/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-6">
                            <div class="form-group">
                                <label>PIC</label>
                                <v-select v-model="form.idPegawai" :options="picOptions" :settings="picSetting" @search:focus="maybeLoadPIC"/>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-6">
                            <div class="form-group">
                                <label>Tanggal</label>
                                <Datepicker v-model="form.tanggal" :format="'dd/MM/yyyy'" :input-class="'form-control'"/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-3">
                            <div class="form-group">
                                <label>Tipe Nominal</label>
                                <select class="form-control" v-model="form.nominalType">
                                    <option value="d">Debit</option>
                                    <option value="k">Kredit</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-9">
                            <div class="form-group">
                                <label>Nominal</label>
                                <div class="input-group">
                                    <span class="input-group-addon">Rp</span>
                                    <input v-model="form.nominal" type="number" min="0" class="form-control">
                                    <span class="input-group-addon">,00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea v-model="form.keterangan" class="form-control" rows="3" placeholder="Isikan Keterangan ..."></textarea>
                    </div> 
                    <div class="form-group" v-if="jenisTransaksi!=2 && jenisTransaksi!=null && form.nominalType!=null">
                        <label v-if="form.nominalType == 'd' && form.nominalType!=null">Simpan ke Bank</label>
                        <label v-if="form.nominalType == 'k' && form.nominalType!=null">Ambil dari Bank</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                            <input type="checkbox" v-model="form.isInvolvedBank">
                            </span>
                            <v-select ref="select" :disabled="!form.isInvolvedBank" v-model="form.idSimpanan" :options="simpananOptions" @search:focus="maybeLoadSimpanan"/>
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
    import Form from 'vform';
    import Datepicker from 'vuejs-datepicker';
    export default {
        components:{
            Datepicker
        },
        data:()=>({
            form: new Form({
                idKategori: 0,
                idPegawai: null,
                tanggal: null,
                nominalType: null,
                nominal: 0,
                keterangan: null,
                idSimpanan: null,
                idTransaksi: null,
                idProyek: null,
                isKategoriChanged: false,
                isNominalChanged: false,
                isInvolvedBank: false
            }),
            jenisTransaksi: null,
            idKat:null,
            kategoriDisabled: true,
            kategoriOptions: [],
            picOptions: [],
            simpananOptions:[],
            kategoriSetting: {
                width:'100%',
                placeholder:'Cari Kategori ...'
            },
            picSetting: {
                width:'100%',
                placeholder:'Cari Nama PIC ...'
            },
            primary: 'primary',
        }),

        created(){
        },
        methods:{
            changeJenisTransaksi(){
                this.kategoriDisabled = false;
                this.kategoriOptions=[];
                this.form.idKategori=null;
                this.form.keterangan= null;
                this.form.idSimpanan= null;
                this.form.isInvolvedBank= false;
            },
            changeSelectedKategori(){
               if(this.jenisTransaksi==1){
                   this.form.idKategori = this.idKat
               }else if(this.jenisTransaksi==2){
                   this.form.idSimpanan = this.idKat
               }else if(this.jenisTransaksi==3){
                   this.form.idProyek = this.idKat
               }
            },
            storeTransaksi(){
                let self = this
                let url = 'api/transaksi/store'
                let date = new Date(this.form.tanggal)
                this.form.post(url)
                    .then(({data})=>{
                        this.$swal(
                            'Tersimpan!',
                            'Transaksi '+this.form.keterangan+' telah berhasil disimpan.',
                            'success'
                        )
                        this.form.reset()
                        this.$parent.getAllTransaksi(date.getFullYear());
                    }) 
            },
            maybeLoadPIC() {
                return this.picOptions.length <= 0 ? this.getAllPegawaiList() : null
            },
            maybeLoadKategori() {
                return this.kategoriOptions.length <= 0 ? this.getAllKategoriList(this.jenisTransaksi) : null
            },
            maybeLoadSimpanan(){
                return this.simpananOptions.length <= 0 ? this.getAllSimpananList() : null
            },
            getAllSimpananList(){
                let url = '/api/transaksibank/getallsimpananlistnoncash';
                let self = this
                this.$refs.select.toggleLoading(true)
                fetch(url)
                  .then(res => res.json())
                  .then(res => {
                    let data = res.data;
                    for(var i = 0; i < data.length; i++){
                        self.simpananOptions.push({
                            label : data[i]['nama_bank'],
                            value : data[i].id
                        })
                    }
                    this.$refs.select.toggleLoading(false)
                  })
                  .catch(err => console.log(err));
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
            getAllKategoriList(idTrans){
                console.log(idTrans)
                let url = '';
                var name = '';
                switch (+idTrans){
                    case 1:
                        url = '/api/transaksiumum/getallkategorilist';
                        name = 'nama_kategori';
                        break;
                    case 2:
                        url = '/api/transaksibank/getallsimpananlistnoncash';
                        name = 'nama_bank';
                        break;
                    case 3:
                        var dt = Date.now();
                        url = '/api/transaksiproyek/getallproyeklist?tanggal='+dt;
                        name = 'nama_proyek';
                        break;
                    default: 
                        url = '';
                        name = '';
                }
                
                let self = this
                this.$refs.select2.toggleLoading(true)
                fetch(url)
                  .then(res => res.json())
                  .then(res => {
                    let data = res.data;
                    console.log(data)
                    for(var i = 0; i < data.length; i++){
                        self.kategoriOptions.push({
                            label : data[i][name],
                            value : data[i].id
                        })
                    }
                    this.$refs.select2.toggleLoading(false)
                  })
                  .catch(err => console.log(err));
            }
        }
    }
    $(function() {
        $('#datepicker').datepicker()
    });
</script>

<style>
.v-select .dropdown-toggle {
    display: flex !important;
}
.v-select .selected-tag {
    overflow: hidden;
    text-overflow: ellipsis; 
    width: 100%;
}
.v-select input {
    width: 1px !important;
}
</style>