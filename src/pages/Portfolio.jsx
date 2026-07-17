import React, { useState } from 'react';
import styles from './Portfolio.module.css';

// Centralized portfolio data with demo URLs (subdomain-based)
const portfolioItems = [
  {
    id: 'hris',
    title: 'HRIS & Absensi',
    category: 'HRIS',
    modules: '12 Modul',
    desc: 'Sistem informasi sumber daya manusia terpadu yang mencakup manajemen kehadiran, cuti, lembur, dan penggajian.',
    img: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDG5onik6UmShtNvs08pYcb1SjWSXu-aeuvFy-Z5SL0ddNrodUdrYRej0gYrh94upxa165N2E5B48_ut_qqXKlCm37nmzPz1EuvvnmZGeKusRv5cZJJS5xEEY8AHxZX768aMupSvvytsuLkrHMZTMtabKBW7U4psQGkJywW6ON6AZc0YjlIKzWSPPnV69P1PHuhg518ilfRv3w37OLJwiTNKUj1b_qe-3h00l4eBL8v3w7Wn6KUy7b-5ZXyQc9xvJVRMFoiqXpOVjo',
    demoUrl: 'https://hris.moduvox.com',
    techStack: ['Laravel', 'MySQL', 'Bootstrap'],
    status: 'active',       // active | coming-soon | maintenance
    projectDir: 'projects/hris',
  },
  {
    id: 'siardi',
    title: 'SIARDI',
    category: 'Dokumen',
    modules: '5 Modul',
    desc: 'Platform arsip digital cerdas untuk pengindeksan, pencarian, dan pengamanan dokumen institusi dengan hak akses berlapis.',
    img: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCvkJssdONfsXVPckXiaDSlWBo-GEs_6th48mRTV_WK6RpSWhJaJE4qhSVYeJ40D6V0nK3rCHCEN3tPv5WUsyuMx6ri52NyPs6nEnltRMyaC2rrx3IsHOiynZr4a_pMgD4yT2GvR9BJwiMWtRZ4NNLMeEcj5-CSWE1rFnxn4Y3Sg9393eN8OFtfLUVRBpUL7f85MIc5uDOwq67dadlIMg2SW6cDwCTk8i_o-E-P_Wbxaz9QnvbbcB0BDYouW-b65UJAmFTMT2mee3g',
    demoUrl: 'https://siardi.moduvox.com',
    techStack: ['Laravel', 'MySQL', 'Vue.js'],
    status: 'active',
    projectDir: 'projects/siardi',
  },
  {
    id: 'crm',
    title: 'CRM',
    category: 'CRM',
    modules: '8 Modul',
    desc: 'Manajemen hubungan pelanggan komprehensif untuk melacak prospek, interaksi layanan, dan pipeline penjualan perusahaan.',
    img: 'https://lh3.googleusercontent.com/aida-public/AB6AXuA0qzXlHrEdjju_DTgLse8wHAZE9zJbwORRlxsPBQhPeFNONbTAgjWYZWUyiNnAbWiJTf-v4ORpSU594IlW1kwv_pJSFs0d48wdYw2-ovUNpt65MFR3GRVEtvZYcuAdwAD9zwpIe2n9TCtDsY0ElQRrHd4MhKxJG-tjKBghCVThA7sb3570cNgpK2028D4eo2vDW94M8tFAUCFVirKMtrpO0oKbsMwQJNhHIBt4We0sFtE_pX6zwiYGjDRjImfGfkLl-dchHmjSaW8',
    demoUrl: 'https://crm.moduvox.com',
    techStack: ['Laravel', 'PostgreSQL', 'Livewire'],
    status: 'active',
    projectDir: 'projects/crm',
  },
  {
    id: 'core-banking',
    title: 'Core Banking Koperasi',
    category: 'Perbankan',
    modules: '15 Modul',
    desc: 'Sistem operasi perbankan inti yang dirancang khusus untuk lembaga keuangan mikro dan koperasi dengan standar keamanan tinggi.',
    img: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDH_zTyPREQaL-lmsSSYxPns67hwjFtNyfXlroDEE2xUHG9-s_JGFi8pQpIwFw-N9t576MTM7PTmjA3ZjvprbIK0ceD48vxiiYO6Ba1k91IgCsb6CkGq_z9faKdym15GHJx5n5koJYNTFPuLvET2Iwbz3cpS9IezN-DlZS1bTYYCmxVYDoZiPzGFJd23gKDBQ8vifAMIKNQEdZvY2FxzYoxZ69upBOPX8BoQANC_gL1_DKQuMg1MQ96esTZJDn6IvEh7-wHtHTCkZY',
    demoUrl: 'https://core-banking.moduvox.com',
    techStack: ['Laravel', 'MySQL', 'Bootstrap'],
    status: 'active',
    projectDir: 'projects/core-banking',
  },
  {
    id: 'bantuan-potong',
    title: 'Aplikasi Bantuan Potong',
    category: 'Perbankan',
    modules: '4 Modul',
    desc: 'Modul pendukung transaksi perbankan untuk otomasi pemotongan angsuran dan rekonsiliasi data penggajian instansi.',
    img: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAXBJMsbxqMgDc9r8rTHljenaqKi0mWYbl7aMq6wYxIfIrY01dX6fLcA6T7Ll7RKkYpAZPdpkmoPx_Lk-UBPNnSj0GJ0BU8kRDWkG0h5w04XlAMxJqvWwXBt8VkcnDpHvjOvfMocceFiWUdF0gYE__JQfsmgsCBlQfll0Hj6UawZ5XtQZSMNa0HeUK6B3Ks-6goyR1F-agcrKI7NyJD-64b0vEOaSsDzGcDFkilMXTIUoe0S78kmKIN6bSvwqbEVZuxOIze14Hvees',
    demoUrl: 'https://bantuan-potong.moduvox.com',
    techStack: ['Laravel', 'MySQL'],
    status: 'active',
    projectDir: 'projects/bantuan-potong',
  },
  {
    id: 'sinergi',
    title: 'Sinergi',
    category: 'Kolaborasi',
    modules: '6 Modul',
    desc: 'Platform kolaborasi internal untuk manajemen tugas terpusat, komunikasi tim, dan pemantauan metrik operasional harian.',
    img: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBrAjNzqqqFoeYzjOIJnQW_UhK-wPMMXXntmqL6ebb3_0sQHBQu9O-H-jB5vOWBRB-UFdfe-yJxieNsadDB8cMQdgqtJW5F8O5VezXzWj5LDQss76yC-w2dLfhprnZMKxet0vurrkjg6QjWniwn-aOqPXjHAdU0mscQlQKTfPBmr82BPl1PvI0fchqmSW858dQFlKcyOi3_btGInNLgAG6ao1cdGw3cwqD42tHWmJKpmZoihvCI1ZbBGtzdSbQV6ucU4MYktiPkwHc',
    demoUrl: 'https://sinergi.moduvox.com',
    techStack: ['Laravel', 'MySQL', 'Livewire'],
    status: 'active',
    projectDir: 'projects/sinergi',
  },
  {
    id: 'moduvox-channeling',
    title: 'Moduvox Channeling',
    category: 'Channeling',
    modules: '3 Modul',
    desc: 'Gateway integrasi aman untuk komunikasi data dua arah yang stabil antara sistem internal dengan ekosistem perbankan eksternal.',
    img: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBaZSx9ht69iSNEuWHMb1pu12IClRMl72PcwQF91Cz6-1lqznJSz-WVGYCBPUB2yYAwgCDph-ZK61DFsYX_cBErQEpjseshF3xaYMqzC0sFfQGIfS08EMVPkFvoUyiUiDrBtFA0i7_00sJ7syVlzmKZtKqTl6evb3Gl3zIBRt9lHFpRGAWIbOCL9yABiQmL0pqNJt13E7uUaMHzNJKJlkrFgXm_XlgaS4s9C9ABnNZRhb6k9e6nNK-_tzRQ1_Nt1EcYW0yPqz6YJng',
    demoUrl: 'https://channeling.moduvox.com',
    techStack: ['Laravel', 'MySQL', 'REST API'],
    status: 'active',
    projectDir: 'projects/moduvox-channeling',
  },
  {
    id: 'web-company-profile',
    title: 'Web Company Profile',
    category: 'CMS',
    modules: '2 Modul',
    desc: 'Sistem manajemen konten dinamis (CMS) untuk portal informasi publik perusahaan dengan performa tinggi dan desain responsif.',
    img: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDvYlw5hv5p3Mn7PjzmQ1lW9AmZ-DY3jpy9sKEO4Pa_h79zKizy4EhTDeRkZOmKxhdnHE26uL19dYn7xpd_01J6W1QxgnFfqI2Qu-dl6wUL1tGXoB6qiUASW1khbEHljI8XmMI30A0uPD3cBgt1DUd-jDvsivGC3Nck16RBUi91XnYUY_c9ySSzmdj11PfoAqR_9iov82N1qC-cIKbltLcq8oIaWB18h6aBml7Nu7LH1v__swuwaPkiLA46VRdBtivWO7qTrizF3ZM',
    demoUrl: 'https://wcp.moduvox.com',
    techStack: ['Laravel', 'MySQL', 'Bootstrap'],
    status: 'active',
    projectDir: 'projects/web-company-profile',
  }
];

const statusConfig = {
  active: { label: 'Demo Aktif', color: '#00A86B', bg: '#ECFDF5' },
  'coming-soon': { label: 'Segera Hadir', color: '#F59E0B', bg: '#FFFBEB' },
  maintenance: { label: 'Maintenance', color: '#94A3B8', bg: '#F1F5F9' },
};

export default function Portfolio() {
  const [activeCategory, setActiveCategory] = useState('Semua');
  const [searchQuery, setSearchQuery] = useState('');

  const categories = ['Semua', ...new Set(portfolioItems.map(item => item.category))];

  const filteredItems = portfolioItems.filter(item => {
    const matchCategory = activeCategory === 'Semua' || item.category === activeCategory;
    const matchSearch = item.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
                        item.desc.toLowerCase().includes(searchQuery.toLowerCase());
    return matchCategory && matchSearch;
  });

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
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
          />
        </div>
        <div className={styles.filters}>
          {categories.map((cat) => (
            <button 
              key={cat} 
              className={`${styles.filterBtn} ${activeCategory === cat ? styles.active : ''}`}
              onClick={() => setActiveCategory(cat)}
            >
              {cat}
            </button>
          ))}
        </div>
      </section>

      <section className={styles.grid}>
        {filteredItems.map((item) => {
          const status = statusConfig[item.status];
          return (
            <div key={item.id} className={styles.card}>
              <div className={styles.cardHeader}>
                <div className={styles.iconWrapper}>
                  <img alt={item.category} src={item.img} />
                </div>
                <span 
                  className={styles.statusBadge}
                  style={{ background: status.bg, color: status.color }}
                >
                  <span className={styles.statusDot} style={{ background: status.color }}></span> 
                  {status.label}
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

                {/* Tech Stack Tags */}
                <div className={styles.techTags}>
                  {item.techStack.map((tech) => (
                    <span key={tech} className={styles.techTag}>{tech}</span>
                  ))}
                </div>
              </div>
              
              {item.status === 'active' ? (
                <a 
                  href={item.demoUrl} 
                  target="_blank" 
                  rel="noopener noreferrer"
                  className={styles.cardAction}
                >
                  Masuk Demo <span className={`material-symbols-outlined ${styles.actionIcon}`}>arrow_forward</span>
                </a>
              ) : (
                <button className={styles.cardAction} disabled style={{ opacity: 0.5, cursor: 'not-allowed' }}>
                  {status.label} <span className={`material-symbols-outlined ${styles.actionIcon}`}>schedule</span>
                </button>
              )}
            </div>
          );
        })}
      </section>
    </div>
  );
}
