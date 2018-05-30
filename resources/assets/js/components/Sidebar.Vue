<template>
<!-- sidebar: style can be found in sidebar.less -->
<section class="sidebar">
    <!-- sidebar menu: : style can be found in sidebar.less -->
    <ul class="sidebar-menu" data-widget="tree">
    <li class="header">MAIN NAVIGATION</li>
    <li>
        <router-link :to="{ name: 'transaksi' }">
          <i class="fa fa-exchange"></i> <span>Transaksi</span>
        </router-link>
    </li>
    <li class="treeview menu-open">
        <a href="javascript:;">
        <i class="fa fa-history"></i>
        <span>Riwayat</span>
        <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
        </span>
        </a>
        <ul class="treeview-menu" style="display:block">
        <li><router-link :to="{ name: 'transaksiumum' }"><i class="fa fa-circle-o"></i> Transaksi Umum</router-link></li>
        <li><router-link :to="{ name: 'transaksiproyek' }"><i class="fa fa-circle-o"></i> Transaksi Proyek</router-link></li>
        <li><router-link :to="{ name: 'transaksibank' }"><i class="fa fa-circle-o"></i> Transaksi Bank</a></router-link></li>
        </ul>
    </li>
    <li>
        <router-link :to="{ name: 'pengajuan' }">
        <i class="fa fa-clipboard"></i> <span>Daftar Pengajuan</span>
        </router-link>
    </li>
    <li>
        <router-link :to="{ name: 'kategori' }">
        <i class="fa fa-th"></i> <span>Kategori Umum</span>
        </router-link>
    </li>
    <li>
        <router-link :to="{ name: 'proyek' }">
        <i class="fa fa-line-chart"></i> <span>Proyek</span>
        </router-link>
    </li>
    <li>
        <router-link :to="{ name: 'simpanan' }">
        <i class="fa fa-money"></i> <span>Simpanan</span>
        </router-link>
    </li>
    <li>
        <router-link :to="{ name: 'rekap' }">
          <i class="fa fa-dashboard"></i> <span>Rekap Laporan</span>
        </router-link>
    </li>
    </ul>
</section>
<!-- /.sidebar -->
</template>

