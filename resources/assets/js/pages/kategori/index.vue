<template>
    <!-- TABLE: Transaksi umum -->
    <section class="content">
        <div class="row">
            <tambah/>
        </div>
        <div class="row">
            <Tables :kategoriList="kategoriList" :links="links" :meta="meta" :pages="pages" :prevDisabled="prevPage" :nextDisabled="nextPage" :isloading="isloading"/>
            <div class="modal fade" id="modal-edit">
                <div class="modal-dialog" style="margin-top:150px">
                    <div class="modal-content">
                    <form @submit.prevent="submitEdit"  @keydown="form.onKeydown($event)">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Edit Kategori</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Kategori</label>
                            <input class="form-control" type="text" v-model="form.namaKategori">
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
    </section>
    <!-- /.box -->
</template>

<script>
    import Tambah from '~/pages/kategori/tambah'
    import Tables from '~/pages/kategori/table'
    import Form from 'vform'
    export default {
        middleware: 'auth',
        layout: 'default',
        components: {
            Tambah,
            Tables
        },
        data: () => ({
            isloading:true,
            kategoriList: [],
            links:[],
            prevPage: true,
            nextPage: true,
            pages: [],
            meta:[],
            form: new Form({
                idKategori: 0,
                namaKategori: ''
            }),
        }),
        methods: {
            getAllKategoriList(pageLink){
                this.isloading = true;
                console.log(pageLink)
                let url = pageLink || '/api/transaksiumum/getallkategorilist'
                fetch(url)
                  .then(res => res.json())
                  .then(res => {
                    console.log(res)
                    let data = res.data;
                    this.kategoriList = data; 
                    this.links = res.links;
                    this.meta = res.meta;
                    this.pages = Array.from({length: this.meta.last_page}, (v, i) => i)
                    if(res.meta.current_page == 1) this.prevPage = true
                    else this.prevPage = false
                    if(res.meta.current_page == res.meta.last_page) this.nextPage = true
                    else this.nextPage = false
                    this.isloading = false
                  })
                  .catch(err => console.log(err));
            },
            editModalShow(){
                $("#modal-edit").modal('show')
            },
            submitEdit(){
                let url = 'api/transaksiumum/editkategori'
                this.form.patch(url)
                    .then(({data})=>{
                        this.getAllKategoriList()
                        $("#modal-edit").modal('hide')
                    })
                    .catch(err => console.log(err));
            },
            deleteAlertShow(){
                this.$swal({
                    title: 'Hapus Kategori '+this.form.namaKategori+'?',
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
                let url = 'api/transaksiumum/deletekategori'
                this.form.patch(url)
                    .then(({data})=>{
                        this.getAllKategoriList()
                        this.$swal(
                            'Terhapus!',
                            'Kategori '+this.form.namaKategori+' telah berhasil dihapus.',
                            'success'
                        )
                    })
                    .catch(err => console.log(err));
            }
        }
    }
</script>