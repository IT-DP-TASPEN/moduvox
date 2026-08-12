import React, { useState, useEffect, useRef } from 'react';
import ShowroomHero from '../components/home/ShowroomHero';
import ProductShowcase from '../components/showcase/ProductShowcase';
import WhatWeBuild from '../components/home/WhatWeBuild';
import CapabilityMatrix from '../components/home/CapabilityMatrix';
import Methodology from '../components/home/Methodology';
import Consultation from '../components/home/Consultation';
import { products } from '../data/productData';

// Wave Divider Component
function WaveDivider({ fromColor = '#FFFFFF', toColor = '#F1F5F9', flipped = false }) {
  return (
    <div className="section-divider" style={flipped ? { transform: 'rotate(180deg)' } : {}}>
      <svg viewBox="0 0 1440 48" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,0 C360,48 1080,48 1440,0 L1440,48 L0,48 Z" fill={toColor} />
      </svg>
    </div>
  );
}

export default function Home() {
  const [activeSection, setActiveSection] = useState(0);
  const sectionRefs = useRef([]);

  // Map product IDs for dot nav
  const navItems = products.map(p => ({ id: p.id, label: p.name }));

  useEffect(() => {
    const handleScroll = () => {
      const scrollY = window.scrollY + window.innerHeight / 2;
      
      for (let i = sectionRefs.current.length - 1; i >= 0; i--) {
        const el = sectionRefs.current[i];
        if (el && el.offsetTop <= scrollY) {
          setActiveSection(i);
          break;
        }
      }
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const scrollToSection = (index) => {
    const el = sectionRefs.current[index];
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  };

  return (
    <div>
      <ShowroomHero />

      {/* === WHAT WE BUILD === */}
      <WhatWeBuild />

      {/* === DOT NAVIGATION (Side) === */}
      <nav className="dot-nav" aria-label="Section navigation">
        {navItems.map((item, i) => (
          <button
            key={item.id}
            className={`dot-nav-item ${activeSection === i ? 'active' : ''}`}
            onClick={() => scrollToSection(i)}
            aria-label={item.label}
          >
            <span className="dot-tooltip">{item.label}</span>
          </button>
        ))}
      </nav>

      {/* === DIVIDER: What We Build → Showcases === */}
      <WaveDivider fromColor="#FFFFFF" toColor="#F1F5F9" />

      {/* === PRODUCT SHOWCASES (Experience Center) === */}
      <div id="showcases">
        <div className="container" style={{ textAlign: 'center', marginBottom: '2rem', paddingTop: '4rem' }}>
          <div className="section-eyebrow">
            <span className="section-eyebrow-dot" />
            Experience Center
          </div>
          <h2 style={{ fontSize: '2.5rem', fontWeight: 800, color: 'var(--foreground)' }}>
            Proof of Capability
          </h2>
          <p style={{ fontSize: '1.1rem', color: 'var(--text-muted)', maxWidth: '600px', margin: '1rem auto' }}>
            Jelajahi dan coba langsung sistem enterprise yang telah kami kembangkan.
          </p>
        </div>

        {products.map((product, index) => (
          <div
            key={product.id}
            ref={el => sectionRefs.current[index] = el}
          >
            <ProductShowcase product={product} index={index} />
          </div>
        ))}
      </div>

      {/* === CASE STUDIES BANNER (Proof of Deployment) === */}
      <div style={{ padding: '6rem 0', background: '#F8FAFC', textAlign: 'center', borderTop: '1px solid var(--border)' }}>
        <div className="container">
          <div className="section-eyebrow" style={{ display: 'inline-flex', marginBottom: '1rem' }}>
            <span className="section-eyebrow-dot" />
            Live Implementations
          </div>
          <h2 style={{ fontSize: '2.5rem', fontWeight: 800, color: 'var(--foreground)', marginBottom: '1.5rem' }}>
            Proof of Deployment
          </h2>
          <p style={{ fontSize: '1.2rem', color: 'var(--text-muted)', maxWidth: '700px', margin: '0 auto 2.5rem auto' }}>
            Sistem yang kami bangun bukan sekadar prototipe. Lihat bagaimana solusi enterprise kami diimplementasikan dan menyelesaikan masalah operasional dunia nyata.
          </p>
          <a href="/case-studies" className="btn-primary" style={{ padding: '1rem 2.5rem', fontSize: '1.1rem' }}>
            Lihat Case Studies
          </a>
        </div>
      </div>

      {/* === DIVIDER === */}
      <WaveDivider fromColor="#F8FAFC" toColor="#FFFFFF" />

      <div id="capabilities">
        <CapabilityMatrix />
      </div>

      {/* === DIVIDER: Capabilities → Methodology === */}
      <WaveDivider fromColor="#FFFFFF" toColor="#F8FAFC" />

      <div id="methodology">
        <Methodology />
      </div>

      <Consultation />
    </div>
  );
}
