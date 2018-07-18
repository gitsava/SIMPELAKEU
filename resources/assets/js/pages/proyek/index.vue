<template>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Daftar Proyek</h3>
                    </div>
                    <!-- /.box-header -->
                    <div v-if="isloading" class="overlay">
                        <i class="fa fa-refresh fa-spin"></i>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-xs-12 col-md-6">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon">Ketua Peneliti</span>
                                        <v-select ref="select" v-model="peneliti" :options="options" @search:focus="loadOptions"/>
                                    </div>
                                </div>
                            </div>
							<div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon">Tahun</span>
                                        <input type="text" class="form-control" v-model="tahun" />
                                    </div>
                                </div>
                            </div>
							<div class="col-xs-12 col-md-1">
								<div class="form-group">
									<v-button :type="'primary'"  :method="searchProyek">Cari</v-button>
								</div>
							</div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12 col-md-12">
                                <div class="table-responsive">
                                    <table class="table no-margin">
                                        <thead>
                                        <tr>
                                        <th>No</th>
										<th>Ketua Peneliti </th>
                                        <th>Nama Proyek</th>
										<th>Tahun</th>
                                        <th>Saldo</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <template v-if="!empty" v-for="(proyek, i) in proyekList">
                                                <tr>
                                                    <td>{{ (10*(meta.current_page-1))+i+1 }}</td>
													<td>{{ proyek.peneliti.penelitipsb.pegawai.nama }}</td>
                                                    <td style="width:500px">{{ proyek.kegiatan.nama_kegiatan }}</td>
													<td>{{ (new Date(proyek.kegiatan.tanggal_awal).getFullYear()) + 
													    ' - ' + (new Date(proyek.kegiatan.tanggal_akhir).getFullYear())}}</td>
                                                    <td>{{ proyek.kegiatan.saldo | currency }}</td>
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
                        <ul v-if="!empty" class="pagination pagination-sm no-margin">
                            <li :class="{'disabled': prevDisabled}" style="cursor:pointer" @click="changePage(links.prev)"><a>«</a></li>
                            <li :class="{'disabled': page == meta.current_page-1}" style="cursor:pointer" v-for="page in pages" @click="changePage('/api/transaksiproyek/getallproyeklist?page='+(page+1),true)"><a>{{ page+1 }}</a></li>
                            <li :class="{'disabled': nextDisabled}" style="cursor:pointer" :disabled="meta.current_page == meta.last_page" @click="changePage(links.next)"><a>»</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="empty || proyekList.length<=5" style="margin-bottom:350px"></div>
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
            peneliti: null,
            proyekList: [],
            pages : {},
            isloading: false,
            empty: true,
            nextDisabled: false,
            prevDisabled: false,
            currentPageDisabled: false,
            meta: {},
            links: {},
            hidden: true,
			options: [],
			tahun: null
        }),
        created(){
            
            Cookies.set('p', 4, { expires: null })
        },
        methods: {
           loadData: function(loadStatus){
            console.log(loadStatus)
            this.isloading = !loadStatus
           },
		   loadOptions(){
				return this.options.length <= 0 ? this.populateOptions() : null
		   },
		   populateOptions(){
				let url = '/api/transaksiproyek/getallpeneliti';
                let self = this
                this.$refs.select.toggleLoading(true)
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
                    this.$refs.select.toggleLoading(false)
                  })
                  .catch(err => console.log(err));
		   },
           changePage(link,number=false){
               if(number){
					if(this.peneliti!=null){
						link = link +'&idPeneliti='+ this.peneliti['value'] +'&tahun=' + this.tahun
					}
               }
               this.getAllProyekList(link)
           },
           searchProyek(){
               let url = '/api/transaksiproyek/getallproyeklist?idPeneliti=' + this.peneliti['value'] +'&tahun=' + this.tahun
               this.getAllProyekList(url)
           },
           getAllProyekList(pageLink){
                this.isloading = true;
                let url = pageLink || '/api/transaksiproyek/getallproyeklist'
                fetch(url)
                  .then(res => res.json())
                  .then(res => {
                    console.log(res)
                    this.empty = res.empty
                    if(this.empty){
                        this.proyekList = []
                    }
                    else{
                        let data = res.data;
                        this.proyekList = data.data; 
                        this.links = data;
                        this.meta = data;
                        this.pages = Array.from({length: this.meta.last_page}, (v, i) => i)
                        if(this.meta.current_page == 1) this.prevDisabled = true
                        else this.prevDisabled = false
                        if(this.meta.current_page == this.meta.last_page) this.nextDisabled = true
                        else this.nextDisabled = false
                    }
                    this.isloading = false
                  })
                  .catch(err => console.log(err));
            },
        }
    }
</script>