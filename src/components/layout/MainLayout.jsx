import React, { useState } from 'react';
import { Outlet, Link } from 'react-router-dom';
import { Building2, ChevronDown, Menu, X } from 'lucide-react';
import { products } from '../../data/productData';
import styles from './MainLayout.module.css';

export default function MainLayout() {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  const toggleMobileMenu = () => {
    setIsMobileMenuOpen(!isMobileMenuOpen);
  };

  const closeMobileMenu = () => {
    setIsMobileMenuOpen(false);
  };

  const handleScrollToConsultation = (e) => {
    e.preventDefault();
    closeMobileMenu();
    if (window.location.pathname !== '/') {
      window.location.href = '/#consultation';
    } else {
      const el = document.getElementById('consultation');
      if (el) el.scrollIntoView({ behavior: 'smooth' });
    }
  };

  return (
    <div className={styles.layout}>
      <header className={styles.header}>
        <div className={`container ${styles.headerContainer}`}>
          <Link to="/" className={styles.logo} onClick={closeMobileMenu}>
            <Building2 size={24} className={styles.logoIcon} />
            <div className={styles.logoText}>
              <span className={styles.logoTitle}>Moduvox</span>
              <span className={styles.logoSubtitle}>Enterprise Systems</span>
            </div>
          </Link>

          <nav className={styles.nav}>
            <Link to="/" className={styles.navLink}>Home</Link>
            <Link to="/portfolio" className={styles.navLink}>Portofolio</Link>
            <a href="/#consultation" className={styles.navLink} onClick={handleScrollToConsultation}>Kontak</a>
            <a href="/#consultation" className="btn-primary" onClick={handleScrollToConsultation}>Konsultasi</a>
          </nav>

          <button className={styles.mobileMenuBtn} onClick={toggleMobileMenu}>
            {isMobileMenuOpen ? <X size={24} color="var(--text)" /> : <Menu size={24} color="var(--text)" />}
          </button>
        </div>

        {/* Mobile Dropdown Menu */}
        {isMobileMenuOpen && (
          <div className={styles.mobileNav}>
            <Link to="/" className={styles.mobileNavLink} onClick={closeMobileMenu}>Home</Link>
            <Link to="/portfolio" className={styles.mobileNavLink} onClick={closeMobileMenu}>Portofolio</Link>
            <a href="/#consultation" className={styles.mobileNavLink} onClick={handleScrollToConsultation}>Kontak</a>
            <a href="/#consultation" className={`btn-primary ${styles.mobileNavBtn}`} onClick={handleScrollToConsultation}>Konsultasi</a>
          </div>
        )}
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
                <li key={p.id}><Link to={`/solutions/${p.id}`} onClick={closeMobileMenu}>{p.name}</Link></li>
              ))}
            </ul>
          </div>
          <div className={styles.footerLinks}>
            <h4 className={styles.footerLinksTitle}>Lainnya</h4>
            <ul>
              {products.slice(4).map(p => (
                <li key={p.id}><Link to={`/solutions/${p.id}`} onClick={closeMobileMenu}>{p.name}</Link></li>
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
