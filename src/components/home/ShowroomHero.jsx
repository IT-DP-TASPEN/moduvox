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
      padding: '8rem 0 6rem 0',
      background: '#FFFFFF',
      position: 'relative',
      overflow: 'hidden',
    }}>
      {/* Subtle gradient accent */}
      <div style={{
        position: 'absolute', top: 0, right: 0, width: '40%', height: '100%',
        background: 'linear-gradient(135deg, rgba(0,91,172,0.03) 0%, rgba(14,165,233,0.03) 100%)',
        zIndex: 0,
      }} />

      <div className="container-wide" style={{ position: 'relative', zIndex: 1 }}>
        <div style={{
          display: 'grid',
          gridTemplateColumns: '1fr 1fr',
          gap: '4rem',
          alignItems: 'center',
          marginBottom: '2rem'
        }}>
          
          {/* Left Column: Text Content */}
          <motion.div
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.7 }}
            style={{ maxWidth: '600px' }}
          >
            <div style={{
              display: 'inline-flex', alignItems: 'center', gap: '0.5rem',
              padding: '0.375rem 1rem',
              background: 'var(--surface-alt)',
              border: '1px solid var(--border)',
              borderRadius: '999px',
              marginBottom: '2rem',
            }}>
              <span style={{ width: 8, height: 8, borderRadius: '50%', background: 'var(--primary)' }} />
              <span style={{ fontSize: '0.8rem', fontWeight: 600, color: 'var(--primary)' }}>
                7 Produk Enterprise Siap Operasional
              </span>
            </div>

            <h1 style={{
              fontSize: '3.5rem', fontWeight: 800, lineHeight: 1.1,
              color: 'var(--foreground)', letterSpacing: '-0.03em',
              marginBottom: '1.5rem',
            }}>
              Enterprise Application
              <br />
              <span style={{ color: 'var(--primary)' }}>Experience Center</span>
            </h1>

            <p style={{
              fontSize: '1.2rem', color: 'var(--muted-foreground)', lineHeight: 1.7,
              marginBottom: '2.5rem',
            }}>
              Jelajahi langsung aplikasi enterprise yang telah kami bangun dan operasikan.
              Dari HRIS, CRM, Core Banking hingga sistem perbankan terintegrasi —
              semuanya dapat Anda coba secara interaktif di sini.
            </p>

            <div style={{ display: 'flex', gap: '1rem', marginBottom: '2rem' }}>
              <a href="#showcases" className="btn-primary" style={{ padding: '0.875rem 2rem', fontSize: '1rem' }}>
                Jelajahi Produk
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
              >
                <InteractiveAppWindow 
                  appName={`${carouselProducts[currentProductIndex].name} - Live Preview`}
                  modules={carouselProducts[currentProductIndex].modules[carouselProducts[currentProductIndex].roles[0].id]}
                  color={carouselProducts[currentProductIndex].color}
                  compact={true}
                />
              </motion.div>
            </AnimatePresence>
          </motion.div>
          
        </div>

        {/* Stats */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.7, delay: 0.5 }}
          style={{
            display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)',
            gap: '2rem',
            padding: '2rem 0',
            borderTop: '1px solid var(--border)',
            maxWidth: '1000px'
          }}
        >
          {stats.map((stat, i) => (
            <div key={i}>
              <div style={{ fontSize: '2.5rem', fontWeight: 800, color: 'var(--foreground)', letterSpacing: '-0.02em' }}>
                <AnimatedCounter value={stat.value} suffix={stat.suffix || ''} duration={1.5} />
              </div>
              <div style={{ fontSize: '0.875rem', color: 'var(--muted-foreground)', fontWeight: 500 }}>{stat.label}</div>
            </div>
          ))}
        </motion.div>
      </div>
    </section>
  );
}
