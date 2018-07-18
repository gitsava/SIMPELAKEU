<template>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Daftar Unit</h3>
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
                                        <span class="input-group-btn"><v-button :type="success" :loading="isloading" :method="searchunit">Cari</v-button></span>
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
                                        <th>Nama Unit</th>
                                        <th>Saldo</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <template v-if="!empty" v-for="(unit, i) in unitList">
                                                <tr>
                                                    <td>{{ (10*(meta.current_page-1))+i+1 }}</td>
                                                    <td style="width:700px">{{ unit.nama }}</td>
                                                    <td>{{ unit.saldo | currency}}</td>
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
                            <li :class="{'disabled': page == meta.current_page-1}" style="cursor:pointer" v-for="page in pages" @click="changePage('/api/transaksiunit/getallunitlist?page='+(page+1),true)"><a>{{ page+1 }}</a></li>
                            <li :class="{'disabled': nextDisabled}" style="cursor:pointer" :disabled="meta.current_page == meta.last_page" @click="changePage(links.next)"><a>»</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="empty" style="margin-bottom:300px"></div>
    </section>
</template>

<script>
    import Cookies from 'js-cookie'
    export default {
        layout: 'default',
        middleware: 'bendahara',
        props: {
        },
        data: () => ({
            success : 'success',
            searchKey: null,
            unitList: [],
            pages : {},
            isloading: false,
            empty: true,
            nextDisabled: false,
            prevDisabled: false,
            currentPageDisabled: false,
            meta: {},
            links: {},
            hidden: true
        }),
        created(){
            this.getAllunitList()
            Cookies.set('p', 5, { expires: null })
        },
        methods: {
           loadData: function(loadStatus){
            console.log(loadStatus)
            this.isloading = !loadStatus
           },
           changePage(link,number=false){
               if(number){
                if(this.searchKey!=null){
                    link = link +'&key='+ this.searchKey
                }
               }
               this.getAllunitList(link)
           },
           searchunit(){
               let url = '/api/transaksiunit/getallunitlist?key=' + this.searchKey
               this.getAllunitList(url)
           },
           getAllunitList(pageLink){
                this.isloading = true;
                let url = pageLink || '/api/transaksiunit/getallunitlist'
                fetch(url)
                  .then(res => res.json())
                  .then(res => {
                    console.log(res)
                    this.empty = res.empty
                    if(this.empty){
                        this.unitList = []
                    }
                    else{
                        let data = res.data;
                        this.unitList = data; 
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
        }
    }
</script>