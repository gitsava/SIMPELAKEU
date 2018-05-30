<template>
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Daftar Kategori Transaksi Umum</h3>
            </div>
            <!-- /.box-header -->
            <div v-if="isloading" class="overlay">
                <i class="fa fa-refresh fa-spin"></i>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12 col-md-4">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                                <input type="text" class="form-control" v-model="searchKey" placeholder="Cari disini...">
                                <span class="input-group-btn"><v-button :type="success" :loading="isloading" :method="searchKategori">Cari</v-button></span>
                            </div>
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
                                <th>Nama Kategori</th>
                                <th>Saldo</th>
                                <th>Aksi</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <template v-for="(kategori, i) in kategoriList">
                                        <tr>
                                            <td>{{ (10*(meta.current_page-1))+i+1 }}</td>
                                            <td>{{ kategori.nama_kategori }}</td>
                                            <td>{{ kategori.saldo }}</td>
                                            <td>
                                                <button type="button" id="edit" class="btn btn-box-tool" v-on:click="edit(i)"><i class="fa fa-edit"></i></button>
                                                <!--<button type="button" class="btn btn-box-tool" v-on:click="deleteAlert(i)"><i class="fa fa-trash"></i></button>-->
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /.table-responsive -->
            </div>
            <!-- /.box-body -->
            <div class="box-footer clearfix">
                <ul class="pagination pagination-sm no-margin">
                    <li :class="{'disabled': prevDisabled}" style="cursor:pointer" @click="changePage(links.prev)"><a>«</a></li>
                    <li :class="{'disabled': page == meta.current_page-1}" style="cursor:pointer" v-for="page in pages" @click="changePage('/api/transaksiumum/getallkategorilist?page='+(page+1),true)"><a>{{ page+1 }}</a></li>
                    <li :class="{'disabled': nextDisabled}" style="cursor:pointer" :disabled="meta.current_page == meta.last_page" @click="changePage(links.next)"><a>»</a></li>
                </ul>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        layout: 'default',
        props: {
            hidden:{
                type: Boolean,
                default: true
            },
            kategoriList: {
                type: Array,
                default : []
            },
            links:{},
            meta:{},
            currentPageDisabled:{
                type: Boolean,
                default: false
            },
            prevDisabled:{
                type: Boolean,
                default: false
            },
            nextDisabled:{
                type: Boolean,
                default: false
            },
            pages : {},
            isloading: {
                type: Boolean,
                default : false
            },
        },
        data: () => ({
            success : 'success',
            searchKey: null
        }),
        created(){
            this.$parent.getAllKategoriList()
        },
        methods: {
           loadData: function(loadStatus){
            console.log(loadStatus)
            this.isloading = !loadStatus
           },
           edit: function(i){
            this.$parent.form.idKategori = this.kategoriList[i]['id']
            this.$parent.form.namaKategori = this.kategoriList[i]['nama_kategori']
            this.$parent.editModalShow()
           },
           deleteAlert : function(i){
               this.$parent.form.idKategori = this.kategoriList[i]['id']
               this.$parent.form.namaKategori = this.kategoriList[i]['nama_kategori']
               this.$parent.deleteAlertShow()
           },
           changePage(link,number=false){
               if(number){
                if(this.searchKey!=null){
                    link = link +'&key='+ this.searchKey
                }
               }
               this.$parent.getAllKategoriList(link)
           },
           searchKategori(){
               let url = '/api/transaksiumum/getallkategorilist?key=' + this.searchKey
               this.$parent.getAllKategoriList(url)
           }
        }
    }
    $(function() {
        $('input[id="reservation"]').daterangepicker();
    });
</script>