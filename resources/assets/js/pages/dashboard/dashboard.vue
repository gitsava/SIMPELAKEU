<template>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Grafik Riwayat Keuangan</h3>
                    </div>
                    <!-- /.box-header -->
                    <div v-if="isloading" class="overlay">
                        <i class="fa fa-refresh fa-spin"></i>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-xs-12 col-md-5">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon">Simpanan</span>
                                        <v-select ref="select"  v-model="simpanan" :options="simpananOptions" @search:focus="maybeLoadSimpanan"/>
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
                                    <button type="button" class="btn btn-primary" @click="fillChartData">Tampilkan</button>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <bar-chart :chart-data="chartData" :options="options"></bar-chart>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div style="margin-bottom:62px"></div>
    </section>
</template>
<script>
    import BarChart from './BarChart.js'
    import Cookies from 'js-cookie'
    export default {
        layout: 'default',
        middleware: 'eksekutif',
        components: {
            BarChart
        },
        data: ()=>({
            chartData: null,
            options: {responsive: true, maintainAspectRatio: false, legend: {display: false}},
            simpananOptions: [],
            tahun: null,
            simpanan: null,
            isloading: false
        }),
        created(){
            Cookies.set('p', 0, { expires: null })
			this.$toasted.success('Welcome ' + this.$store.getters['auth/user'].username).goAway(3000)
        },
        methods: {
            fillChartData(){
				console.log(this.simpanan.value)
                let url = 'api/historisimpanan/fetch?tahun=' + this.tahun + '&simpanan=' + this.simpanan.value
				console.log(url)
                this.isloading = true
                fetch(url)
                    .then(res => res.json())
                    .then(res => {
                        var data = res.data
						var empty = res.empty
						if(!empty){
							this.chartData = {
								labels: ['January', 'February', 'March', 'April', 'May', 'June',
										 'July', 'August', 'September', 'October', 'November', 'December'],
								datasets: [
									{
									label: null,
									backgroundColor: '#f87979',
									data: Object.values(data)
									}
								]
							}
							console.log(this.chartData.datasets[0].data)
						}else{
							this.$swal(
								'Gagal',
								'Tidak ada data yang tersedia!',
								'error'
							)
						}
                        this.isloading = false
                    })
            },
            maybeLoadSimpanan(){
                return this.simpananOptions.length <= 0 ? this.populateSimpananOptions() : null
            },
            populateSimpananOptions(){
                let url = '/api/transaksibank/getallsimpananlist';
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
            }
        }
    }
</script>