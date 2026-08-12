import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { ArrowDown } from 'lucide-react';
import AnimatedCounter from '../ui/AnimatedCounter';
import InteractiveAppWindow from '../showcase/InteractiveAppWindow';
import { products } from '../../data/productData';

export default function ShowroomHero() {
  const [currentProductIndex, setCurrentProductIndex] = useState(0);
  
  // Use the first 4 products for the carousel (HRIS, SIARDI, CRM, Core Banking)
  const carouselProducts = products.slice(0, 4);

  useEffect(() => {
    const interval = setInterval(() => {
      setCurrentProductIndex((prevIndex) => (prevIndex + 1) % carouselProducts.length);
    }, 5000); // 5 seconds

    return () => clearInterval(interval);
  }, [carouselProducts.length]);

  const stats = [
    { label: 'Enterprise Products', value: 7 },
    { label: 'Klien Aktif', value: 50, suffix: '+' },
    { label: 'Transaksi Diproses', value: 2.5, suffix: 'M+' },
    { label: 'Tahun Pengalaman', value: 8, suffix: '+' },
  ];

  return (
    <section style={{
      padding: '2.5rem 0 0 0',
      background: '#FFFFFF',
      position: 'relative',
      overflow: 'hidden',
    }}>
      {/* === GRID PATTERN BACKGROUND === */}
      <div style={{
        position: 'absolute', inset: 0,
        backgroundImage: `
          linear-gradient(rgba(0,91,172,0.03) 1px, transparent 1px),
          linear-gradient(90deg, rgba(0,91,172,0.03) 1px, transparent 1px)
        `,
        backgroundSize: '48px 48px',
        zIndex: 0,
      }} />

      {/* === MESH GRADIENT ACCENTS === */}
      <div style={{
        position: 'absolute', top: '-20%', right: '-10%', width: '600px', height: '600px',
        background: 'radial-gradient(circle, rgba(0,91,172,0.06) 0%, transparent 70%)',
        borderRadius: '50%', zIndex: 0, filter: 'blur(40px)',
      }} />
      <div style={{
        position: 'absolute', bottom: '-10%', left: '-5%', width: '400px', height: '400px',
        background: 'radial-gradient(circle, rgba(14,165,233,0.05) 0%, transparent 70%)',
        borderRadius: '50%', zIndex: 0, filter: 'blur(40px)',
      }} />

      <div className="container-wide" style={{ position: 'relative', zIndex: 1 }}>
        <div className="responsive-grid" style={{ marginBottom: '2rem' }}>
          
          {/* Left Column: Text Content */}
          <motion.div
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.7 }}
            style={{ maxWidth: '600px' }}
          >
            <div className="section-eyebrow">
              <span className="section-eyebrow-dot" />
              Enterprise Technology, Built for Real Operations
            </div>

            <h1 style={{
              fontSize: '3.5rem', fontWeight: 800, lineHeight: 1.1,
              color: 'var(--foreground)', letterSpacing: '-0.03em',
              marginBottom: '1.5rem',
            }}>
              Membangun Sistem
              <br />
              <span style={{
                background: 'linear-gradient(135deg, var(--primary), var(--secondary))',
                WebkitBackgroundClip: 'text',
                WebkitTextFillColor: 'transparent',
                backgroundClip: 'text',
              }}>Enterprise</span>
            </h1>

            <p style={{
              fontSize: '1.2rem', color: 'var(--text-muted)', lineHeight: 1.7,
              marginBottom: '2.5rem',
            }}>
              Kami merancang, membangun, dan mengimplementasikan aplikasi enterprise yang disesuaikan dengan proses bisnis — dari HRIS, core operations, hingga sistem khusus untuk kebutuhan organisasi.
            </p>

            <div style={{ display: 'flex', gap: '1rem', marginBottom: '2rem' }}>
              <a href="#showcases" className="btn-primary" style={{ padding: '0.875rem 2rem', fontSize: '1rem' }}>
                Jelajahi Experience Center
                <ArrowDown size={18} />
              </a>
              <a href="#consultation" className="btn-outline" style={{ padding: '0.875rem 2rem', fontSize: '1rem' }}>
                Konsultasi Kebutuhan
              </a>
            </div>
          </motion.div>

          {/* Right Column: Live Product Carousel */}
          <motion.div
            initial={{ opacity: 0, x: 30 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.7, delay: 0.3 }}
            style={{ position: 'relative', height: '500px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}
          >
            <AnimatePresence mode="wait">
              <motion.div
                key={currentProductIndex}
                initial={{ opacity: 0, scale: 0.98, filter: 'blur(4px)' }}
                animate={{ opacity: 1, scale: 1, filter: 'blur(0px)' }}
                exit={{ opacity: 0, scale: 1.02, filter: 'blur(4px)' }}
                transition={{ duration: 0.8, ease: "easeInOut" }}
                style={{ width: '100%', position: 'absolute' }}
                className="app-window-float"
              >
                <InteractiveAppWindow 
                  appName={`${carouselProducts[currentProductIndex].name} - Live Preview`}
                  modules={carouselProducts[currentProductIndex].modulesDetail.slice(0, 4).map((mod, i) => ({
                    id: `mod-${i}`,
                    label: mod.title,
                    icon: mod.icon,
                    widgets: [
                      { label: 'Active Users', value: 1250, suffix: '' },
                      { label: 'System Health', value: 99.9, suffix: '%' }
                    ],
                    table: {
                      title: `Data ${mod.title}`,
                      headers: ['ID', 'Description', 'Status'],
                      rows: [
                        ['#1001', 'System Integration', 'Active'],
                        ['#1002', 'Data Sync Process', 'Active'],
                        ['#1003', 'Security Scan', 'Completed']
                      ]
                    }
                  }))}
                  color={carouselProducts[currentProductIndex].color}
                  compact={true}
                />
              </motion.div>
            </AnimatePresence>

            {/* Carousel Indicators */}
            <div style={{
              position: 'absolute', bottom: '-1.5rem',
              display: 'flex', gap: '0.5rem',
            }}>
              {carouselProducts.map((_, i) => (
                <button
                  key={i}
                  onClick={() => setCurrentProductIndex(i)}
                  style={{
                    width: i === currentProductIndex ? '24px' : '8px',
                    height: '8px',
                    borderRadius: '999px',
                    border: 'none',
                    background: i === currentProductIndex ? 'var(--primary)' : 'var(--border)',
                    cursor: 'pointer',
                    transition: 'all 300ms ease',
                  }}
                />
              ))}
            </div>
          </motion.div>
          
        </div>

        {/* Stats Section with border top & bottom */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.7, delay: 0.5 }}
          className="stats-grid"
          style={{
            padding: '2.5rem 0',
            borderTop: '1px solid var(--border)',
            maxWidth: '1000px'
          }}
        >
          {stats.map((stat, i) => (
            <div key={i}>
              <div style={{ fontSize: '2.5rem', fontWeight: 800, color: 'var(--foreground)', letterSpacing: '-0.02em' }}>
                <AnimatedCounter value={stat.value} suffix={stat.suffix || ''} duration={1.5} />
              </div>
              <div style={{ fontSize: '0.875rem', color: 'var(--text-muted)', fontWeight: 500 }}>{stat.label}</div>
            </div>
          ))}
        </motion.div>

        {/* Text-based Trust Statement */}
        <motion.div
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.7, delay: 0.7 }}
          style={{
            textAlign: 'center',
            marginTop: '3rem',
            paddingTop: '2rem',
            borderTop: '1px solid var(--border)',
            color: 'var(--text-muted)',
            fontWeight: 500,
            letterSpacing: '0.05em',
            textTransform: 'uppercase',
            fontSize: '0.875rem'
          }}
        >
          Trusted Across Banking, Financial Services & Enterprise Operations
        </motion.div>
      </div>

      {/* === WAVE DIVIDER at bottom === */}
      <div className="section-divider" style={{ marginTop: '2rem' }}>
        <svg viewBox="0 0 1440 48" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0,0 C360,48 1080,48 1440,0 L1440,48 L0,48 Z" fill="#F1F5F9" />
        </svg>
      </div>
    </section>
  );
}
