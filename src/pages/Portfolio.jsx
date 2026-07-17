import React from 'react';
import styles from './Portfolio.module.css';

export default function Portfolio() {
  const categories = ['Semua', 'HRIS', 'Perbankan', 'CRM', 'Dokumen', 'Channeling', 'Mobile'];

  const portfolioItems = [
    {
      title: 'HRIS & Absensi',
      category: 'HRIS',
      modules: '12 Modul',
      desc: 'Sistem informasi sumber daya manusia terpadu yang mencakup manajemen kehadiran, cuti, lembur, dan penggajian.',
      img: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDG5onik6UmShtNvs08pYcb1SjWSXu-aeuvFy-Z5SL0ddNrodUdrYRej0gYrh94upxa165N2E5B48_ut_qqXKlCm37nmzPz1EuvvnmZGeKusRv5cZJJS5xEEY8AHxZX768aMupSvvytsuLkrHMZTMtabKBW7U4psQGkJywW6ON6AZc0YjlIKzWSPPnV69P1PHuhg518ilfRv3w37OLJwiTNKUj1b_qe-3h00l4eBL8v3w7Wn6KUy7b-5ZXyQc9xvJVRMFoiqXpOVjo'
    },
    {
      title: 'SIARDI',
      category: 'Dokumen',
      modules: '5 Modul',
      desc: 'Platform arsip digital cerdas untuk pengindeksan, pencarian, dan pengamanan dokumen institusi dengan hak akses berlapis.',
      img: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCvkJssdONfsXVPckXiaDSlWBo-GEs_6th48mRTV_WK6RpSWhJaJE4qhSVYeJ40D6V0nK3rCHCEN3tPv5WUsyuMx6ri52NyPs6nEnltRMyaC2rrx3IsHOiynZr4a_pMgD4yT2GvR9BJwiMWtRZ4NNLMeEcj5-CSWE1rFnxn4Y3Sg9393eN8OFtfLUVRBpUL7f85MIc5uDOwq67dadlIMg2SW6cDwCTk8i_o-E-P_Wbxaz9QnvbbcB0BDYouW-b65UJAmFTMT2mee3g'
    },
    {
      title: 'CRM',
      category: 'CRM',
      modules: '8 Modul',
      desc: 'Manajemen hubungan pelanggan komprehensif untuk melacak prospek, interaksi layanan, dan pipeline penjualan perusahaan.',
      img: 'https://lh3.googleusercontent.com/aida-public/AB6AXuA0qzXlHrEdjju_DTgLse8wHAZE9zJbwORRlxsPBQhPeFNONbTAgjWYZWUyiNnAbWiJTf-v4ORpSU594IlW1kwv_pJSFs0d48wdYw2-ovUNpt65MFR3GRVEtvZYcuAdwAD9zwpIe2n9TCtDsY0ElQRrHd4MhKxJG-tjKBghCVThA7sb3570cNgpK2028D4eo2vDW94M8tFAUCFVirKMtrpO0oKbsMwQJNhHIBt4We0sFtE_pX6zwiYGjDRjImfGfkLl-dchHmjSaW8'
    },
    {
      title: 'Core Banking Koperasi',
      category: 'Perbankan',
      modules: '15 Modul',
      desc: 'Sistem operasi perbankan inti yang dirancang khusus untuk lembaga keuangan mikro dan koperasi dengan standar keamanan tinggi.',
      img: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDH_zTyPREQaL-lmsSSYxPns67hwjFtNyfXlroDEE2xUHG9-s_JGFi8pQpIwFw-N9t576MTM7PTmjA3ZjvprbIK0ceD48vxiiYO6Ba1k91IgCsb6CkGq_z9faKdym15GHJx5n5koJYNTFPuLvET2Iwbz3cpS9IezN-DlZS1bTYYCmxVYDoZiPzGFJd23gKDBQ8vifAMIKNQEdZvY2FxzYoxZ69upBOPX8BoQANC_gL1_DKQuMg1MQ96esTZJDn6IvEh7-wHtHTCkZY'
    },
    {
      title: 'Aplikasi Bantuan Potong',
      category: 'Perbankan',
      modules: '4 Modul',
      desc: 'Modul pendukung transaksi perbankan untuk otomasi pemotongan angsuran dan rekonsiliasi data penggajian instansi.',
      img: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAXBJMsbxqMgDc9r8rTHljenaqKi0mWYbl7aMq6wYxIfIrY01dX6fLcA6T7Ll7RKkYpAZPdpkmoPx_Lk-UBPNnSj0GJ0BU8kRDWkG0h5w04XlAMxJqvWwXBt8VkcnDpHvjOvfMocceFiWUdF0gYE__JQfsmgsCBlQfll0Hj6UawZ5XtQZSMNa0HeUK6B3Ks-6goyR1F-agcrKI7NyJD-64b0vEOaSsDzGcDFkilMXTIUoe0S78kmKIN6bSvwqbEVZuxOIze14Hvees'
    },
    {
      title: 'Sinergi',
      category: 'Semua',
      modules: '6 Modul',
      desc: 'Platform kolaborasi internal untuk manajemen tugas terpusat, komunikasi tim, dan pemantauan metrik operasional harian.',
      img: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBrAjNzqqqFoeYzjOIJnQW_UhK-wPMMXXntmqL6ebb3_0sQHBQu9O-H-jB5vOWBRB-UFdfe-yJxieNsadDB8cMQdgqtJW5F8O5VezXzWj5LDQss76yC-w2dLfhprnZMKxet0vurrkjg6QjWniwn-aOqPXjHAdU0mscQlQKTfPBmr82BPl1PvI0fchqmSW858dQFlKcyOi3_btGInNLgAG6ao1cdGw3cwqD42tHWmJKpmZoihvCI1ZbBGtzdSbQV6ucU4MYktiPkwHc'
    },
    {
      title: 'BTN Channeling',
      category: 'Channeling',
      modules: '3 Modul',
      desc: 'Gateway integrasi aman untuk komunikasi data dua arah yang stabil antara sistem internal dengan ekosistem perbankan eksternal.',
      img: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBaZSx9ht69iSNEuWHMb1pu12IClRMl72PcwQF91Cz6-1lqznJSz-WVGYCBPUB2yYAwgCDph-ZK61DFsYX_cBErQEpjseshF3xaYMqzC0sFfQGIfS08EMVPkFvoUyiUiDrBtFA0i7_00sJ7syVlzmKZtKqTl6evb3Gl3zIBRt9lHFpRGAWIbOCL9yABiQmL0pqNJt13E7uUaMHzNJKJlkrFgXm_XlgaS4s9C9ABnNZRhb6k9e6nNK-_tzRQ1_Nt1EcYW0yPqz6YJng'
    },
    {
      title: 'Web Company Profile',
      category: 'Semua',
      modules: '2 Modul',
      desc: 'Sistem manajemen konten dinamis (CMS) untuk portal informasi publik perusahaan dengan performa tinggi dan desain responsif.',
      img: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDvYlw5hv5p3Mn7PjzmQ1lW9AmZ-DY3jpy9sKEO4Pa_h79zKizy4EhTDeRkZOmKxhdnHE26uL19dYn7xpd_01J6W1QxgnFfqI2Qu-dl6wUL1tGXoB6qiUASW1khbEHljI8XmMI30A0uPD3cBgt1DUd-jDvsivGC3Nck16RBUi91XnYUY_c9ySSzmdj11PfoAqR_9iov82N1qC-cIKbltLcq8oIaWB18h6aBml7Nu7LH1v__swuwaPkiLA46VRdBtivWO7qTrizF3ZM'
    }
  ];

  return (
    <div className={styles.container}>
      <section className={styles.headerSection}>
        <h1 className={styles.title}>Portal Demo Aplikasi</h1>
        <p className={styles.subtitle}>
          Jelajahi seluruh solusi enterprise yang telah kami bangun dan rasakan pengalaman menggunakan setiap aplikasi melalui simulasi interaktif.
        </p>
      </section>

      <section className={styles.searchSection}>
        <div className={styles.searchBox}>
          <span className={`material-symbols-outlined ${styles.searchIcon}`}>search</span>
          <input 
            className={styles.searchInput} 
            placeholder="Cari aplikasi..." 
            type="text"
          />
        </div>
        <div className={styles.filters}>
          {categories.map((cat, idx) => (
            <button 
              key={cat} 
              className={`${styles.filterBtn} ${idx === 0 ? styles.active : ''}`}
            >
              {cat}
            </button>
          ))}
        </div>
      </section>

      <section className={styles.grid}>
        {portfolioItems.map((item, idx) => (
          <div key={idx} className={styles.card}>
            <div className={styles.cardHeader}>
              <div className={styles.iconWrapper}>
                <img alt={item.category} src={item.img} />
              </div>
              <span className={styles.statusBadge}>
                <span className={styles.statusDot}></span> Demo Aktif
              </span>
            </div>
            
            <div className={styles.cardContent}>
              <h3 className={styles.cardTitle}>{item.title}</h3>
              <div className={styles.cardMeta}>
                <span>{item.category}</span>
                <span className={styles.dot}></span>
                <span>{item.modules}</span>
              </div>
              <p className={styles.cardDesc}>{item.desc}</p>
            </div>
            
            <button className={styles.cardAction}>
              Masuk Demo <span className={`material-symbols-outlined ${styles.actionIcon}`}>arrow_forward</span>
            </button>
          </div>
        ))}
      </section>
    </div>
  );
}
