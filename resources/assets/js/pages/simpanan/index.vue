<template>
    <!-- TABLE: Transaksi umum -->
    <section class="content">
        <div class="row">
            <tambah/>
        </div>
        <div class="row">
            <Tables :simpananList="simpananList" :links="links" :meta="meta" :pages="pages" :prevDisabled="prevPage" :nextDisabled="nextPage" :isloading="isloading" :empty="empty"/>
            <div class="modal fade" id="modal-edit">
                <div class="modal-dialog" style="margin-top:150px">
                    <div class="modal-content">
                    <form @submit.prevent="submitEdit"  @keydown="form.onKeydown($event)">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Edit Simpanan</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Simpanan</label>
                            <input class="form-control" type="text" v-model="form.namaSimpanan">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                        <v-button :type="'primary'" :loading="form.busy">Save changes</v-button>
                    </div>
                    </form>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->
        </div>
        <div v-if="empty" style="margin-bottom:300px"></div>
    </section>
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
    import Tambah from '~/pages/simpanan/tambah'
    import Tables from '~/pages/simpanan/table'
    import Form from 'vform'
    import Cookies from 'js-cookie'
    export default {
        middleware: 'auth',
        layout: 'default',
        components: {
            Tambah,
            Tables
        },
        data: () => ({
            isloading:true,
            simpananList: [],
            links:[],
            prevPage: true,
            nextPage: true,
            pages: [],
            meta:[],
            form: new Form({
                idSimpanan: 0,
                namaSimpanan: ''
            }),
            empty: true
        }),
        created(){
            Cookies.set('p', 6, { expires: null })
        },
        methods: {
            getAllSimpananList(pageLink){
                this.isloading = true;
                console.log(pageLink)
                let url = pageLink || '/api/transaksibank/getallsimpananlist'
                fetch(url)
                  .then(res => res.json())
                  .then(res => {
                    console.log(res)
                    this.empty = res.empty
                    if(this.empty){
                        this.simpananList = []
                    }
                    else{
                        let data = res.data;
                        this.simpananList = data; 
                        this.links = res.links;
                        this.meta = res.meta;
                        this.pages = Array.from({length: this.meta.last_page}, (v, i) => i)
                        if(res.meta.current_page == 1) this.prevPage = true
                        else this.prevPage = false
                        if(res.meta.current_page == res.meta.last_page) this.nextPage = true
                        else this.nextPage = false
                    }
                    this.isloading = false
                  })
                  .catch(err => console.log(err));
            },
            editModalShow(){
                $("#modal-edit").modal('show')
            },
            submitEdit(){
                let url = 'api/transaksibank/editsimpanan'
                this.form.patch(url)
                    .then(({data})=>{
                        this.getAllSimpananList()
                        $("#modal-edit").modal('hide')
                    })
                    .catch(err => console.log(err));
            },
            deleteAlertShow(){
                this.$swal({
                    title: 'Hapus Simpanan '+this.form.namaSimpanan+'?',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.value) {
                            this.submitDelete()        
                        }
                })   
            },
            submitDelete(){
                let url = 'api/transaksibank/deletesimpanan'
                this.form.patch(url)
                    .then(({data})=>{
                        this.getAllSimpananList()
                        this.$swal(
                            'Terhapus!',
                            'Simpanan '+this.form.namaSimpanan+' telah berhasil dihapus.',
                            'success'
                        )
                    })
                    .catch(err => console.log(err));
            }
        }
    }
</script>