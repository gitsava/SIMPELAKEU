<template>
  <div class="">
    <div class="row">
        <div class="col-xs-12 col-md-5">
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon">Proyek</span>
                    <v-select ref="select" style="max-height:36px" v-model="proyek" :options="proyekOptions" :settings="proyekSetting" @search:focus="maybeLoadProyek"/>
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
                <v-button :type="'primary'" :loading="isFilterLoading"  :method="loadData">Filter</v-button>
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
                <v-button :type="'success'" :loading="isCariLoading" :method="loadData" :block="true">Cari</v-button>
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
                    <th>Proyek</th>
                    <!--<th>Aksi</th>-->
                    </tr>
                    </thead>
                    <tbody>
                    <template v-if="!empty" v-for="(item, i) in list">
                        <tr>
                            <td>{{ item.tanggal }}</td>
                            <td>{{ item.pegawai }}</td>
                            <td>{{ item.keterangan }}</td>
                            <template v-if="item.nominal_type == 'd'">
                                <td>{{ item.nominal }}</td>
                                <td>0</td>
                            </template>
                            <template v-if="item.nominal_type == 'k'">
                                <td>0</td>
                                <td>{{ item.nominal }}</td>
                            </template>
                            <template v-if="item.nominal_type == ''">
                                <td></td>
                                <td></td>
                            </template>
                            <td>{{ item.saldo }}</td>
                            <td v-if="item.kategori.length > 17" data-toggle="tooltip" :title="item.kategori">{{ item.kategori.substring(0,17) }}...</td>
                            <td v-if="item.kategori.length <= 17">{{ item.kategori }}</td>
                            <!--<td>
                                <button type="button" id="edit" class="btn btn-box-tool" v-on:click="edit(i)"><i class="fa fa-edit"></i></button>
                                <button type="button" class="btn btn-box-tool" v-on:click="deleteAlert(i)"><i class="fa fa-trash"></i></button>
                            </td>-->
                        </tr>
                    </template>
                    </tbody>
                </table>
                <p v-if="empty" style="text-align:center"> No Records Found. </p>
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
            },
            isFilterLoading:{
                type: Boolean,
                default: false
            },
            isCariLoading:{
                type: Boolean,
                default: false
            },
            list:{},
            empty : {
                type : Boolean,
                default : true
            }
        },
        data: () => ({
            proyekOptions: [],
            proyekSetting: {
                width:'100%'
            },
            proyek: null,
            tahun : 2018
        }),
        methods: {
            loadData: function(loadStatus){
                this.$parent.getAllTransaksiProyek(this.proyek['value'],this.tahun)
            },
            edit: function(status){
                console.log(status)
                this.editClicked = !status
            },
            downloadExcel(){
                var url = "/api/downloadexcel/laporantahunanproyek?tahun="+this.tahun+"&idProyek="+this.proyek['value']
                var result = document.createElement('a'); 
                      result.href = url;
                      result.download = 'Laporan Keuangan '+this.proyek['label']+' '+this.tahun+'.xlsx';
                      result.click();
            },
            maybeLoadProyek(){
                return this.proyekOptions.length <= 0 ? this.populateProyekOptions() : null
            },
            populateProyekOptions(){
                let url = '/api/transaksiproyek/getallproyeklist';
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
    width: 100%;
}
.v-select input {
    width: 1px !important;
}
</style>