<template>
    <div class="col-md-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Daftar Simpanan</h3>
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
                                <span class="input-group-btn"><v-button :type="success" :loading="isloading" :method="searchSimpanan">Cari</v-button></span>
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
                                <th>Nama Simpanan</th>
                                <th>Saldo</th>
                                <th>Aksi</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <template v-if="!empty" v-for="(simpanan, i) in simpananList">
                                        <tr>
                                            <td>{{ (10*(meta.current_page-1))+i+1 }}</td>
                                            <td>{{ simpanan.nama_bank }}</td>
                                            <td>{{ simpanan.saldo | currency }}</td>
                                            <td>
                                                <button type="button" id="edit" class="btn btn-box-tool" v-on:click="edit(i)"><i class="fa fa-edit"></i></button>
                                                <!--<button type="button" class="btn btn-box-tool" v-on:click="deleteAlert(i)"><i class="fa fa-trash"></i></button>-->
                                            </td>
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
                    <li :class="{'disabled': page == meta.current_page-1}" style="cursor:pointer" v-for="page in pages" @click="changePage('/api/transaksiumum/getallsimpananlist?page='+(page+1),true)"><a>{{ page+1 }}</a></li>
                    <li :class="{'disabled': nextDisabled}" style="cursor:pointer" :disabled="meta.current_page == meta.last_page" @click="changePage(links.next)"><a>»</a></li>
                </ul>
            </div>
        </div>
    </div>
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
    export default {
        layout: 'default',
        props: {
            hidden:{
                type: Boolean,
                default: true
            },
            simpananList: {
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
            empty : {
                type : Boolean,
                default : true
            }
        },
        data: () => ({
            success : 'success',
            searchKey: null
        }),
        created(){
            this.$parent.getAllSimpananList()
        },
        methods: {
           loadData: function(loadStatus){
            console.log(loadStatus)
            this.isloading = !loadStatus
           },
           edit: function(i){
            this.$parent.form.idSimpanan = this.simpananList[i]['id']
            this.$parent.form.namaSimpanan = this.simpananList[i]['nama_bank']
            this.$parent.editModalShow()
           },
           deleteAlert : function(i){
               this.$parent.form.idSimpanan = this.simpananList[i]['id']
               this.$parent.form.namaSimpanan = this.simpananList[i]['nama_bank']
               this.$parent.deleteAlertShow()
           },
           changePage(link,number=false){
               if(number){
                if(this.searchKey!=null){
                    link = link +'&key='+ this.searchKey
                }
               }
               this.$parent.getAllSimpananList(link)
           },
           searchSimpanan(){
               let url = '/api/transaksibank/getallsimpananlist?key=' + this.searchKey
               this.$parent.getAllSimpananList(url)
           }
        }
    }
    $(function() {
        $('input[id="reservation"]').daterangepicker();
    });
</script>