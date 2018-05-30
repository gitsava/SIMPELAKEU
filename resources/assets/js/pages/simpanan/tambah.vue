<template>
    <div class="col-md-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Tambah Simpanan</h3>
            </div>
            <!-- /.box-header -->
            <form @submit.prevent="storeSimpanan" @keydown="form.onKeydown($event)">
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-12 col-md-5">
                            <div class="form-group">
                                <label>Nama Simpanan</label>
                                <input class="form-control" type="text" v-model="form.namaSimpanan" placeholder="Isikan nama simpanan ...">
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-5">
                            <div class="form-group">
                                <label>Saldo Awal</label>
                                <input class="form-control" type="text" v-model="form.saldoAwal" placeholder="Isikan jika ada ...">
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-2">
                            <div class="form-group">
                                <label>&nbsp</label>
                                <v-button :type="primary" :loading="form.busy" :block="true">Simpan</v-button>
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

    <!--
    	| Database akun_bank Attribute Information
    	| ________________________________________________
		| id 			    : idSimpanan 
		| nama_bank		    : namaSimpanan
		| saldo	        	: saldo
		| status			: status (active = 1 deleted = 2)
		| ________________________________________________
		| Model             : Simpanan
        | Query search      : key
    -->

<script>
    import Form from 'vform'
    export default {
        data:()=>({
            form: new Form({
                namaSimpanan: '',
                saldo: 0
            }),
            primary: 'primary',
            bankChecked : false
        }),
        methods:{
            storeSimpanan(){
                let self = this
                let url = 'api/transaksibank/storesimpanan'
                this.form.post(url)
                    .then(({data})=>{
                        self.$parent.getAllSimpananList()
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