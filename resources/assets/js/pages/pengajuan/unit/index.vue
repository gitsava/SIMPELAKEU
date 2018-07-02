<template>
    <section class="content">
        <div class="row">
            <Tambah/>
        </div>
        <div class="row">
            <Tables :page="page" :currentPage="currentPage" :indexList="indexList" :isloading="isloading" :list="filteredData"/>
            <div class="modal fade" id="modal-generate">
                <div class="modal-dialog middle" >
                    <div class="modal-content">
                        <div class="modal-header">
                        <h4 class="modal-title" style="text-align:center">Generating Excel File</h4>
                        </div>
                        <div class="modal-body">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%">
                                Please Wait...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script>
    import Tables from '~/pages/pengajuan/unit/table'
    import Tambah from '~/pages/pengajuan/unit/form'
    import Cookies from 'js-cookie'
    export default {
        layout: 'default',
        middleware: 'unit',
        components: {
            Tambah,
            Tables
        },
        metaInfo () {
            return { title: 'Pengajuan Penggunaan Dana' }
        },
        data: () => ({
            
        })
    }
</script>
<style>
  .middle{
    margin: 11% 25%;
  }  
</style>