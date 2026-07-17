import React from 'react';
import { Outlet, Link } from 'react-router-dom';
import { Building2, ChevronDown, Menu } from 'lucide-react';
import { products } from '../../data/productData';
import styles from './MainLayout.module.css';

export default function MainLayout() {
  return (
    <div className={styles.layout}>
      <header className={styles.header}>
        <div className={`container ${styles.headerContainer}`}>
          <Link to="/" className={styles.logo}>
            <Building2 size={24} className={styles.logoIcon} />
            <div className={styles.logoText}>
              <span className={styles.logoTitle}>Moduvox</span>
              <span className={styles.logoSubtitle}>Enterprise Systems</span>
            </div>
          </Link>

          <nav className={styles.nav}>
            <Link to="/" className={styles.navLink}>Home</Link>
            <Link to="/portfolio" className={styles.navLink}>Portofolio</Link>
            <a href="/#consultation" className={styles.navLink}>Kontak</a>
            <a href="/#consultation" className="btn-primary">Konsultasi</a>
          </nav>

          <button className={styles.mobileMenuBtn}>
            <Menu size={24} color="var(--text)" />
          </button>
        </div>
      </header>

      <main className={styles.main}>
        <Outlet />
      </main>

      <footer className={styles.footer}>
        <div className={`container ${styles.footerContainer}`}>
          <div className={styles.footerBrand}>
            <Building2 size={28} className={styles.footerLogoIcon} />
            <h3 className={styles.footerTitle}>Moduvox Enterprise</h3>
            <p className={styles.footerDesc}>
              Enterprise Application Experience Center — Jelajahi langsung solusi perangkat lunak operasional tingkat enterprise yang telah kami bangun dan operasikan.
            </p>
          </div>
          <div className={styles.footerLinks}>
            <h4 className={styles.footerLinksTitle}>Produk</h4>
            <ul>
              {products.slice(0, 4).map(p => (
                <li key={p.id}><Link to={`/solutions/${p.id}`}>{p.name}</Link></li>
              ))}
            </ul>
          </div>
          <div className={styles.footerLinks}>
            <h4 className={styles.footerLinksTitle}>Lainnya</h4>
            <ul>
              {products.slice(4).map(p => (
                <li key={p.id}><Link to={`/solutions/${p.id}`}>{p.name}</Link></li>
              ))}
            </ul>
          </div>
        </div>
        <div className={styles.footerBottom}>
          <div className="container">
            &copy; {new Date().getFullYear()} Moduvox Enterprise. All rights reserved.
          </div>
        </div>
      </footer>
    </div>
  );
}
