<template>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Tambah Pengajuan Dana</h3>
                    </div>
                    <!-- /.box-header -->
                    <div v-if="isloading" class="overlay">
                        <i class="fa fa-refresh fa-spin"></i>
                    </div>
                    <form @submit.prevent="storePengajuan" role="form">
                        <div class="box-body">
                            <div class="row">
                                <div class="col-xs-12 col-md-4">
                                    <div class="form-group">
                                        <label>Ketua Peneliti</label>
                                        <input type="text" class="validation" v-model="form.idPegawai" required/>
                                        <v-select ref="select3" @input="changeKetua" :value="form.idPegawai" :options="options" @search:focus="loadOptions"/>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-4">
                                    <div class="form-group">
                                        <label>Proyek</label>
                                        <input type="text" class="validation" v-model="form.idProyek" required/>
                                        <v-select :disabled="proyekDisabled" ref="select" style="max-height:36px" v-model="form.idProyek" :options="proyekOptions" @search:focus="maybeLoadProyek"/>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-4">
                                    <div class="form-group">
                                        <label>Tanggal Pengajuan</label>
                                        <input type="text" class="validation" v-model="form.tanggal" required/>
                                        <Datepicker v-model="form.tanggal" :format="'dd/MM/yyyy'" :input-class="'form-control bg-datepicker'"/>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <template v-for="(key, index) in arrayField">
                                <div class="row">
                                    <div class="col-xs-1 col-md-1">
                                        <div class="form-group" style="text-align:center">
                                            <label v-if="index == 0" >NO</label>
                                            <input readonly="readonly" class="form-control" :value="index+1"/>
                                        </div>
                                    </div>
                                    <div class="col-xs-3 col-md-3">
                                        <div class="form-group" style="text-align:center">
                                            <label v-if="index == 0">KETERANGAN</label>
                                            <textarea rows="1" class="form-control" v-model="form.keterangan[index]" required/>
                                        </div>
                                    </div>
                                    <div class="col-xs-1 col-md-1">
                                        <div class="form-group" style="text-align:center">
                                            <label v-if="index == 0">JUMLAH</label>
                                            <input type="number" class="form-control" @input="changePerkiraan($event, index)"  v-model="form.jumlah[index].value"/>
                                        </div>
                                    </div>
                                    <div class="col-xs-2 col-md-2">
                                        <div class="form-group" style="text-align:center">
                                            <label v-if="index == 0" >UNIT</label>
                                            <input type="text" class="form-control" v-model="form.unit[index]"/>
                                        </div>
                                    </div>
                                    <div class="col-xs-2 col-md-2">
                                        <div class="form-group" style="text-align:center">
                                            <label v-if="index == 0" >PERKIRAAN BIAYA</label>
                                            <money class="form-control money-text-right" v-model="form.perkiraanBiaya[index].value" @input="changePerkiraan($event, index)" required/>
                                        </div>
                                    </div>
                                    <div class="col-xs-2 col-md-2">
                                        <div class="form-group" style="text-align:center">
                                            <label v-if="index == 0" >SUB TOTAL</label>
                                            <money class="form-control money-text-right" readonly="readonly" v-model="subTotal[index].value"/>
                                        </div>
                                    </div>
                                    <div class="col-xs-1 col-md-1">
                                        <div class="form-group" style="text-align:center">
                                            <label v-if="index == 0">HAPUS</label>
                                            <button v-if="arrayField.length>1" type="button" class="btn btn-box-tool" v-on:click="deleteField($event,index)"><i class="fa fa-times"></i></button>
                                        </div>
                                    </div>
                                </div> 
                            </template>
                            <hr>
                            <div class="row">
                                <div class="col-xs-1 col-md-1 pull-right">
                                    <div style="text-align:center">
                                        <button class="btn btn-primary btn-box-tool" @click.stop="addField($event)" ><i class="fa fa-plus"></i></button>
                                    </div>
                                </div>
                                <div class="col-xs-2 col-md-2 pull-right">
                                    <money class="form-control money-text-right" readonly="readonly" v-model="total"/>
                                </div>
                                <div class="col-xs-2 col-md-2 pull-right">
                                    <h4 class="pull-right" >TOTAL</h4>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <v-button :type="'success'" :loading="form.busy" >Simpan dan Download</v-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div style="margin-bottom:200px"></div>
    </section>
</template>
<script>
    import Form from 'vform';
    import Cookies from 'js-cookie'
    export default {
        middleware: 'asisten',
        layout: 'default',
        created(){
            Cookies.set('p', 0, { expires: null })
            this.initiateArray()
        },
        data: ()=>({
            form: new Form({
                idProyek: 0,
                idPegawai: null,
                tanggal: null,
                perkiraanBiaya: [],
                keterangan: [],
                jumlah: [],
                unit: [],
            }),
            dlForm: new Form({
                transaksiList: []
            }),
            subTotal: [],
            proyekDisabled: true,
            peneliti: [],
            idPeneliti: null,
            arrayField: [1],
            noteAlert: false,
            options: [],
            tahun: new Date().getFullYear(),
            proyekOptions: [],
            isloading: false,
            total: 0,
        }),
        methods: {
            initiateArray(){
                this.form.perkiraanBiaya.push({value:0})
                this.form.keterangan.push('')
                this.form.jumlah.push({value:0})
                this.form.unit.push('')
                this.subTotal.push({value:0})
            },
            spliceArray(index){
                this.form.perkiraanBiaya.splice(index,1)
                this.form.keterangan.splice(index,1)
                this.form.jumlah.splice(index,1)
                this.form.unit.splice(index,1)
                this.subTotal.splice(index,1)
            },
            changeKetua(value){
                this.proyekDisabled = false
                this.form.idPegawai = value
                if(this.form.idPegawai != null){
                    this.idPeneliti = this.peneliti[this.form.idPegawai.value]
                }
                this.proyekOptions = []
                this.form.idProyek = null
            },
            changePerkiraan: function(event,index){
                if(this.form.jumlah[index].value > 0){
                    this.subTotal[index].value = this.form.jumlah[index].value * this.form.perkiraanBiaya[index].value
                }else{
                    this.subTotal[index].value = this.form.perkiraanBiaya[index].value 
                }
                if(this.subTotal[index].hasOwnProperty('value')) {
                    if(this.subTotal[index].value != 0){
                        this.total = this.getSum()
                    }
                }
            },
            getSum(){
                var sum = 0
                for(var i=0; i<this.subTotal.length; i++){
                    sum += this.subTotal[i].value
                }
                return sum
            },
            addField: function(event){
                event.preventDefault()
                this.initiateArray()
                this.arrayField.push(1)
            },
            deleteField: function(event, index){
                event.preventDefault()
                this.arrayField.splice(index,1)
                this.spliceArray(index)
                this.total = this.getSum()
            },
            storePengajuan(){
                let url = 'api/asisten/pengajuan/store'
                this.form.post(url)
                    .then(({data})=>{
                        this.$swal(
                            'Tersimpan!',
                            'Pengajuan dana telah berhasil disimpan.',
                            'success'
                        )
                        this.dlForm.transaksiList = data.data;
                        this.downloadPdf();
                        this.form.reset()
                        this.arrayField = [1]
                        this.subTotal = []
                        this.total = 0
                        this.initiateArray()
                    })
            },
            downloadPdf(){
                let url = 'api/asisten/pengajuan/downloadpdf'
                this.dlForm.post(url)
            },
            loadOptions(){
                return this.options.length <= 0 ? this.populateOptions() : null
            },
            maybeLoadProyek(){
                return this.proyekOptions.length <= 0 ? this.populateProyekOptions() : null
            },
            populateOptions(){
				let url = '/api/transaksiproyek/getallpeneliti';
                let self = this
				this.$refs.select3.toggleLoading(true)
                fetch(url)
                  .then(res => res.json())
                  .then(res => {
                    let data = res.data;
					console.log(data)
                    for(var i = 0; i < data.length; i++){
                        self.options.push({
                            label : data[i].pegawai.nama,
                            value : data[i].pegawai.id
                        })
						self.peneliti[data[i].pegawai.id] = data[i].id_peneliti
                    }
					this.$refs.select3.toggleLoading(false)
                  })
                  .catch(err => console.log(err));
		   },
           populateProyekOptions(){
                let url = '/api/transaksiproyek/getallproyeklist?tahun='+this.tahun+'&idPeneliti='
							  +this.idPeneliti + '&options=true';
                let self = this
                this.$refs.select.toggleLoading(true)
                fetch(url)
                  .then(res => res.json())
                  .then(res => {
                    let data = res.data;
                    let empty = res.empty
                    if(!empty){
                        for(var i = 0; i < data.length; i++){
                            self.proyekOptions.push({
                                label : data[i]['nama_proyek'],
                                value : data[i].id
                            })
                        }
                    }
                    this.$refs.select.toggleLoading(false)
                  })
                  .catch(err => console.log(err));
            }
        }
    }

</script>
<style>
.v-select .dropdown-toggle {
    display: flex !important;
}
.v-select .selected-tag {
    overflow: hidden;
    text-overflow: ellipsis; 
    width: 600%;
}
.v-select input {
    width: 100% !important;
}
.validation {
  position: absolute;
  width: calc(100% - 1px); height: calc(100% - 1px);
  border: none;
  border-radius: 5px;
  background: none;
  left: 0%; bottom: 0;
  z-index: -1;
  opacity: 0;
}
.bg-datepicker {
    background-color: #ffffff !important;
}
.money-text-right {
    text-align: right;
}
</style>
