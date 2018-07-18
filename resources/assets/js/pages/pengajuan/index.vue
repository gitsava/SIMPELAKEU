<template>
    <section class="content">
        <div class="modal fade" id="modal-edit">
            <div class="modal-dialog" style="margin-top:150px">
                <div class="modal-content">
                <form  @submit.prevent="storePengajuan">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Masukan ke Transaksi Proyek</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea v-model="form.keterangan" class="form-control" rows="3" placeholder="Isikan Keterangan ..."></textarea>
                    </div> 
                    <div class="form-group">
                        <label>Nominal</label>
                        <money class="form-control money-text-right" v-model="form.nominal" required/>
                    </div>
                    <div class="form-group">
                        <label>Tipe Nominal</label>
                        <select class="form-control" v-model="form.nominalType">
                            <option value="d">Debit</option>
                            <option value="k">Kredit</option>
                        </select>
                    </div>
                    <div class="form-group" v-if="form.nominalType!=null">
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                    <v-button :type="'primary'" :loading="form.busy">Simpan</v-button>
                </div>
                </form>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Daftar Pengajuan Penggunaan Dana</h3>
                    </div>
                    <!-- /.box-header -->
                    <div v-if="isloading" class="overlay">
                        <i class="fa fa-refresh fa-spin"></i>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-xs-12 col-md-3">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon">Ketua Peneliti</span>
                                        <v-select ref="select1" :value="peneliti" @input="changeKetua" :options="options" @search:focus="loadOptions"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-4">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon">Proyek</span>
                                        <v-select :disabled="proyekDisabled" ref="select" style="max-height:36px" v-model="proyek" :options="proyekOptions" @search:focus="maybeLoadProyek"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-4">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon">Tanggal Pengajuan</span>
                                        <Datepicker v-model="tanggal" :format="'dd/MM/yyyy'" :input-class="'form-control bg-datepicker'"/>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-1">
                                <div class="form-group">
                                    <v-button :type="'primary'"  :method="loadData">Filter</v-button>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-4">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                        <input type="text" v-model="search" class="form-control" placeholder="Cari disini...">
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-1">
                                <div class="form-group">
                                    <button class="btn btn-success" @click="submitSearch" >Cari</button>
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
                                        <template v-if="!empty" v-for="(item, i) in indexList[currentPage]">
                                                <tr>
                                                    <td>{{ filteredData[item].tanggal }}</td>
                                                    <td>{{ filteredData[item].pegawai }}</td>
                                                    <td class="keterangan">{{ filteredData[item].keterangan }}</td>
                                                    <td>{{ filteredData[item].nominal | currency}}</td>
                                                    <td v-if="filteredData[item].kategori.length > 17" data-toggle="tooltip" :title="filteredData[item].kategori">{{ filteredData[item].kategori.substring(0,17) }}...</td>
                                                    <td v-if="filteredData[item].kategori.length <= 17">{{ filteredData[item].kategori }}</td>
                                                    <td>
                                                        <button v-if="filteredData[item].edit_able" type="button" id="edit" class="btn btn-box-tool" v-on:click="edit(item)"><i class="fa fa-edit"></i></button>
                                                        <button v-if="filteredData[item].delete_able" type="button" class="btn btn-box-tool" v-on:click="deleteAlert(item)"><i class="fa fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                        </template>
                                        </tbody>
                                    </table>
                                    <p v-if="empty" style="text-align:center"> No Records Found. </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer clearfix">
                        <ul class="pagination pagination-sm no-margin">
                            <li :class="{'disabled':currentPage == 0}" style="cursor:pointer" @click="changePage(currentPage-1)"><a>«</a></li>
                            <template v-for="n in page">
                                <li :class="{'disabled':currentPage == n-1}" style="cursor:pointer" @click="changePage(n-1)"><a>{{ n }}</a></li>
                            </template>
                            <li :class="{'disabled':currentPage == page-1}" style="cursor:pointer" @click="changePage(currentPage-1)"><a>»</a></li>
                        </ul>
                        <button v-if="!empty" class="btn btn-primary pull-right"  >Download Pdf</button>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="empty || filteredData.length <= 5" style="margin-bottom:300px"></div>
    </section>
</template>

<script>
    import Form from 'vform'
    import Cookies from 'js-cookie'
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
            bankChecked: false,
            tahun: new Date().getFullYear(),
            disDate: null,
            form: new Form({
                idTransaksi: 0,
                nominalType: null,
                idSimpanan: null,
                isInvolvedBank: false,
                keterangan: null,
                nominal: 0,
            }),
            options: [],
            tanggal: null,
            proyekDisabled: true,
            proyekOptions: [],
            simpananOptions:[],
            peneliti: null,
            proyek: null,
            search: null,
            list: [],
            page: 0,
            currentPage: 0,
            indexList: [],
            seq: [],
            limit: 10,
            filteredData: [],
            empty: true,
        }),
        created(){
            Cookies.set('p', 2, { expires: null })
            this.disableDate()
        },
        methods: {
            disableDate(){
                this.disDate = {
                    from: new Date()
                }
            },
            loadData: function(loadStatus){
                this.getAllPenggunaanDana(this.proyek['value'])
            },
            edit: function(i){
                this.form.idTransaksi = this.list[i].id
                this.form.keterangan = this.list[i].keterangan
                this.form.nominal = this.list[i].nominal
                $("#modal-edit").modal('show')  
            },
            changeKetua(value){
				this.peneliti = value
                if(this.peneliti != null){
                    this.proyekDisabled = false
                    
                }else{
                    this.proyekDisabled = true
                }
                this.proyekOptions = []
                this.proyek = null
			},
            loadOptions(){
				return this.options.length <= 0 ? this.populateOptions() : null
		    },
		    populateOptions(){
				let url = '/api/transaksiproyek/getallpeneliti';
				let self = this
				this.$refs.select1.toggleLoading(true)
				fetch(url)
				  .then(res => res.json())
				  .then(res => {
					let data = res.data;
					for(var i = 0; i < data.length; i++){
						self.options.push({
							label : data[i].pegawai.nama,
							value : data[i].id_peneliti
						})
					}
					this.$refs.select1.toggleLoading(false)
				  })
				  .catch(err => console.log(err));
 		    },
            maybeLoadProyek(){
                return this.proyekOptions.length <= 0 ? this.populateProyekOptions() : null
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
            populateProyekOptions(){
                let url = '/api/transaksiproyek/getallproyeklist?tahun='+this.tahun+'&idPeneliti='
							  +this.peneliti.value + '&options=true';
                let self = this
                this.$refs.select.toggleLoading(true)
                fetch(url)
                  .then(res => res.json())
                  .then(res => {
                    let data = res.data;
                    for(var i = 0; i < data.length; i++){
                        self.proyekOptions.push({
                            label : data[i]['nama_proyek'],
                            value : data[i].id
                        })
                    }
                    this.$refs.select.toggleLoading(false)
                  })
                  .catch(err => console.log(err));
            },
            submitSearch(){
                this.filteredData = this.list.filter(data => data.keterangan.toLowerCase().indexOf(this.search.toLowerCase(),0) != -1 &&  data.pegawai != '')
                this.createPagination()
            },
            getAllPenggunaanDana(proyek){
                this.isloading = true
                let tgl = this.tanggal
                let dt =  (tgl.getMonth()+1) + '/' + tgl.getDate() + '/' + tgl.getFullYear()
                let url = proyek == null? '/api/pengajuandanaproyek/fetch' 
                    : '/api/pengajuandanaproyek/fetch?idProyek='+proyek+'&tanggal='+dt
                let self = this
                console.log(dt)
                console.log(this.tanggal)
                fetch(url)
                    .then(res => res.json())
                    .then(res => {
                    let data = res.data;
                    if(!res.empty){
                        this.list = data;
                        this.isloading = false
                        this.empty = false
                    }
                    else{
                        this.list = [];
                        this.isloading = false
                        this.empty = true
                    }
                    this.filteredData = this.list;
                    this.createPagination();
                    })
                    .catch(err => console.log(err));
            },
            createPagination(){
                this.page = Math.ceil(this.filteredData.length/this.limit);
                this.seq = Array.from(new Array(this.filteredData.length),(val,index)=>index);
                this.indexList = []
                for(var i = 0; i<this.page; i++){
                    this.indexList.push(this.seq.splice(0,this.limit))
                }
                this.currentPage = this.page-1
            },
            storePengajuan(){
                let url = 'api/pengajuandanaproyek/store'
                this.form.post(url)
                    .then(({data})=>{
                        this.$swal(
                            'Tersimpan!',
                            'Transaksi '+this.form.keterangan+' telah berhasil disimpan.',
                            'success'
                        )
                        $("#modal-edit").modal('hide')
                        this.form.reset()
                        let proyek = this.proyek == null ? null : this.proyek['value']
                        this.getAllPenggunaanDana(proyek);
                    }) 
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