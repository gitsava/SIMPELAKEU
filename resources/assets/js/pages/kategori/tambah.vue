<template>
    <div class="col-md-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Tambah Kategori Transaksi Umum</h3>
            </div>
            <!-- /.box-header -->
            <form @submit.prevent="storeKategori" @keydown="form.onKeydown($event)">
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-12 col-md-5">
                            <div class="form-group">
                                <label>Nama Kategori</label>
                                <input class="form-control" type="text" v-model="form.namaKategori" placeholder="Isikan nama kategori ...">
                            </div>
                        </div>
                        <!--<div class="col-xs-12 col-md-5">
                            <div class="form-group">
                                <label>Saldo Awal</label>
                                <input class="form-control" type="text" v-model="form.saldo" placeholder="Isikan jika ada ...">
                            </div>
                        </div>-->
                        <div class="col-xs-12 col-md-2">
                            <div class="form-group">
                                <label>&nbsp</label>
                                <v-button :type="primary" :loading="form.busy" :method="loadData" :block="true">Simpan</v-button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.box-body -->
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
        middleware: 'guest',
        data:()=>({
            form: new Form({
                namaKategori: '',
                saldo: 0
            }),
            primary: 'primary',
            bankChecked : false,
            isloading: false
        }),
        methods:{
            loadData: function(loadStatus){
                console.log(loadStatus)
                this.isloading = !loadStatus
            },
            storeKategori(){
                let self = this
                let url = 'api/transaksiumum/storekategori'
                this.form.post(url)
                    .then(({data})=>{
                        self.$parent.getAllKategoriList()
                    })
            }
        }
    }
    $(function() {
        $('#datepicker').datepicker({
            autoclose: true
        })
    });
</script>