<template>
    <div class="col-md-12">
        <div class="box box-success collapsed-box">
            <div class="box-header with-border" data-widget="collapse" style="cursor:pointer">
                <h3 class="box-title">Tambah Transaksi Bank</h3>
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
                                <label>Simpanan</label>
                                <!--<Select2 v-model="form.idKategori" :options="simpananOptions" :settings="simpananSetting" />-->
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-4">
                            <div class="form-group">
                                <label>PIC</label>
                                <!--<Select2 v-model="form.idPegawai" :options="picOptions" :settings="picSetting" />-->
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
                idBank: 0,
                idPegawai: '',
                tanggal: '',
                tipeNominal: 1,
                nominal: 0,
                keterangan: ''
            }),
            simpananOptions: [{
                text: "name1",
                id: "value1"
                }, {
                text: "name2",
                id: "value2"
                }, {
                text: "name3",
                id: "value3"
            }],
            picOptions: [{
                text: "name1",
                id: "value1"
                }, {
                text: "name2",
                id: "value2"
                }, {
                text: "name3",
                id: "value3"
            }],
            simpananSetting: {
                width:'100%',
                placeholder:'Cari Kategori ...'
            },
            picSetting: {
                width:'100%',
                placeholder:'Cari Nama PIC ...'
            },
            primary: 'primary',
            bankChecked : false
        })
    }
    $(function() {
        $('#datepicker').datepicker({
            autoclose: true
        })
    });
</script>