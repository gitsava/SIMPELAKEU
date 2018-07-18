<template>
<!-- sidebar: style can be found in sidebar.less -->
<section class="sidebar">
    <div class="user-panel">
        <div class="pull-left image">
          <img src="/storage/ipb.png" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p>{{ user.username }}</p>
          <a href="#">{{ user.user_role_s_i[0]['user_role']['nama_role'] }}</a>
        </div>
      </div>
    <!-- sidebar menu: : style can be found in sidebar.less -->
    <ul class="sidebar-menu" data-widget="tree">
    <li class="header">MAIN NAVIGATION</li>
    <template v-if="user.user_role_s_i[0].id_role == 8">
        <li :class="{'active':active==0}" @click="activate(0)">
            <router-link :to="{ name: 'transaksi' }">
            <i class="fa fa-exchange"></i> <span>Transaksi</span>
            </router-link>
        </li>
        <li :class="{'active':active==1,'menu-open':menuOpen}" class="treeview" >
            <a href="javascript:;" @click="openMenu()">
            <i class="fa fa-history"></i>
            <span>Riwayat</span>
            <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
            </span>
            </a>
            <ul :class="{'closed':!menuOpen,'opened':menuOpen}" class="treeview-menu">
            <li :class="{'active':treeActive==0}" @click="activateTree(0)"><router-link :to="{ name: 'transaksiumum' }"><i class="fa fa-circle-o"></i> Transaksi Umum</router-link></li>
            <li :class="{'active':treeActive==1}" @click="activateTree(1)"><router-link :to="{ name: 'transaksiproyek' }"><i class="fa fa-circle-o"></i> Transaksi Proyek</router-link></li>
            <li :class="{'active':treeActive==2}" @click="activateTree(2)"><router-link :to="{ name: 'transaksibank' }"><i class="fa fa-circle-o"></i> Transaksi Bank</a></router-link></li>
            <li :class="{'active':treeActive==3}" @click="activateTree(3)"><router-link :to="{ name: 'transaksiunit' }"><i class="fa fa-circle-o"></i> Transaksi Unit</a></router-link></li>
            </ul>
        </li>
        <li :class="{'active':active==2}" @click="activate(2)">
            <router-link :to="{ name: 'pengajuan' }">
            <i class="fa fa-clipboard"></i> <span>Daftar Pengajuan</span>
            </router-link>
        </li>
        <li :class="{'active':active==3}" @click="activate(3)">
            <router-link :to="{ name: 'kategori' }">
            <i class="fa fa-th"></i> <span>Kategori Umum</span>
            </router-link>
        </li>
        <li :class="{'active':active==4}" @click="activate(4)">
            <router-link :to="{ name: 'proyek' }">
            <i class="fa fa-line-chart"></i> <span>Proyek</span>
            </router-link>
        </li>
        <li :class="{'active':active==5}" @click="activate(5)">
            <router-link :to="{ name: 'unit' }">
            <i class="fa fa-clone"></i> <span>Unit</span>
            </router-link>
        </li>
        <li :class="{'active':active==6}" @click="activate(6)">
            <router-link :to="{ name: 'simpanan' }">
            <i class="fa fa-money"></i> <span>Simpanan</span>
            </router-link>
        </li>
        <li :class="{'active':active==7}" @click="activate(7)">
            <router-link :to="{ name: 'rekap' }">
            <i class="fa fa-dashboard"></i> <span>Rekap Laporan</span>
            </router-link>
        </li>
    </template>
    <template v-if="user.user_role_s_i[0].id_role == 9">
        <li :class="{'active':active==0}" @click="activate(0)">
            <router-link :to="{ name: 'tambahpengajuan' }">
            <i class="fa fa-money"></i> <span>Pengajuan Dana</span>
            </router-link>
        </li>
        <li :class="{'active':active==1}" @click="activate(1)">
            <router-link :to="{ name: 'asistenpengajuan' }">
            <i class="fa fa-clipboard"></i> <span>Daftar Pengajuan</span>
            </router-link>
        </li>
    </template>
    <template v-if="user.user_role_s_i[0].id_role == 3">
        <li :class="{'active':active==0}" @click="activate(0)">
            <router-link :to="{ name: 'dashboard' }">
            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
            </router-link>
        </li>
        <li :class="{'active':active==7}" @click="activate(7)">
            <router-link :to="{ name: 'rekap' }">
            <i class="fa fa-clipboard"></i> <span>Rekap Laporan</span>
            </router-link>
        </li>
    </template>
    </ul>
</section>
<!-- /.sidebar -->
</template>

<script>
    import Cookies from 'js-cookie'
    export default {
        
        data:()=>({
            user: null,
            active: null,
            treeActive: null,
            menuOpen: false
        }),
        created(){
            this.user = this.$store.getters['auth/user']
            this.activateSide()
        },
        methods: {
            activate(i){
                this.active = i
                this.treeActive = null
            },
            activateSide(){
                this.active = Cookies.get('p')
                if(this.active == 1){
                    this.menuOpen = true
                    this.treeActive = Cookies.get('t')
                }
            },
            activateTree(i){
                this.treeActive = i
                this.active = 1
            },
            openMenu(){
                this.menuOpen = !this.menuOpen
            }
        }
    }
</script>
<style>
    .closed{
        display: none !important;
    }
    .opened{
        display: block;
    }
</style>