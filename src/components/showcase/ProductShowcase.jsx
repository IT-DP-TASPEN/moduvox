import React, { useState } from 'react';
import { motion, useInView } from 'framer-motion';
import { useRef } from 'react';
import { Link } from 'react-router-dom';
import { ArrowRight, CheckCircle2 } from 'lucide-react';
import InteractiveAppWindow from './InteractiveAppWindow';

export default function ProductShowcase({ product, index }) {
  const isEven = index % 2 === 0;
  const ref = useRef(null);
  const isInView = useInView(ref, { once: true, margin: '-100px' });

  const Icon = product.icon;

  // Alternate between soft-tinted backgrounds for visual rhythm
  const bgColors = [
    '#F1F5F9',   // Slate-100
    '#FFFFFF',   // White
  ];
  const bgColor = bgColors[index % 2];

  // Map modulesDetail to InteractiveAppWindow format
  const mockModules = product.modulesDetail?.slice(0, 4).map((mod, i) => ({
    id: `mod-${i}`,
    label: mod.title,
    icon: mod.icon,
    widgets: [
      { label: 'Active Users', value: 850, suffix: '' },
      { label: 'System Health', value: 99.8, suffix: '%' }
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
  })) || [];

  return (
    <section
      ref={ref}
      id={`showcase-${product.id}`}
      style={{
        padding: '5rem 0',
        background: bgColor,
        overflow: 'hidden',
        position: 'relative',
      }}
    >
      {/* Subtle radial glow behind the app window side */}
      <div style={{
        position: 'absolute',
        top: '50%', 
        transform: 'translateY(-50%)',
        [isEven ? 'right' : 'left']: '-5%',
        width: '500px', height: '500px',
        background: `radial-gradient(circle, ${product.color}08 0%, transparent 70%)`,
        borderRadius: '50%',
        zIndex: 0,
        filter: 'blur(60px)',
      }} />

      <div className="container-wide" style={{ position: 'relative', zIndex: 1 }}>
        <div style={{
          display: 'grid',
          gridTemplateColumns: '1fr 1fr',
          gap: '4rem',
          alignItems: 'center',
        }}>
          {/* Text Content */}
          <motion.div
            initial={{ opacity: 0, x: isEven ? 30 : -30 }}
            animate={isInView ? { opacity: 1, x: 0 } : {}}
            transition={{ duration: 0.6, delay: 0.1 }}
            style={{ order: isEven ? 2 : 1 }}
          >
            {/* Eyebrow */}
            <div style={{
              display: 'inline-flex', alignItems: 'center', gap: '0.5rem',
              padding: '0.375rem 0.875rem',
              background: `${product.color}10`,
              border: `1px solid ${product.color}20`,
              borderRadius: '999px',
              marginBottom: '1.5rem',
            }}>
              <Icon size={14} color={product.color} />
              <span style={{ fontSize: '0.75rem', fontWeight: 700, color: product.color, textTransform: 'uppercase', letterSpacing: '0.06em' }}>
                {product.category || product.name}
              </span>
            </div>

            <h2 style={{
              fontSize: '2.25rem', fontWeight: 800, color: '#1E293B',
              marginBottom: '1rem', lineHeight: 1.2, letterSpacing: '-0.02em',
            }}>
              {product.tagline}
            </h2>

            <p style={{
              fontSize: '1rem', color: '#64748B', lineHeight: 1.7,
              marginBottom: '2rem', maxWidth: '500px',
            }}>
              {product.longDescription?.split('.').slice(0, 2).join('.')}...
            </p>

            <ul style={{ listStyle: 'none', padding: 0, marginBottom: '2rem' }}>
              {product.highlights?.map((highlight, i) => (
                <motion.li
                  key={i}
                  initial={{ opacity: 0, x: -10 }}
                  animate={isInView ? { opacity: 1, x: 0 } : {}}
                  transition={{ duration: 0.4, delay: 0.3 + i * 0.1 }}
                  style={{
                    display: 'flex', alignItems: 'flex-start', gap: '0.75rem',
                    marginBottom: '0.75rem', fontSize: '0.9rem', color: '#334155',
                  }}
                >
                  <CheckCircle2 size={18} color={product.color} style={{ flexShrink: 0, marginTop: 2 }} />
                  <div>
                    <strong>{highlight.title}:</strong> {highlight.desc}
                  </div>
                </motion.li>
              ))}
            </ul>

            <Link
              to={`/solutions/${product.id}`}
              style={{
                display: 'inline-flex', alignItems: 'center', gap: '0.5rem',
                color: product.color, fontWeight: 600, fontSize: '0.9rem',
                transition: 'gap 200ms ease',
              }}
              onMouseEnter={e => e.currentTarget.style.gap = '0.75rem'}
              onMouseLeave={e => e.currentTarget.style.gap = '0.5rem'}
            >
              Lihat Detail Solusi <ArrowRight size={18} />
            </Link>
          </motion.div>

          {/* Interactive App Window — Floating */}
          <motion.div
            initial={{ opacity: 0, x: isEven ? -30 : 30, y: 20 }}
            animate={isInView ? { opacity: 1, x: 0, y: 0 } : {}}
            transition={{ duration: 0.6, delay: 0.3 }}
            style={{ order: isEven ? 1 : 2 }}
            className="app-window-float"
          >
            {product.screenshots && product.screenshots.length > 0 ? (
              <div style={{ position: 'relative', width: '100%' }}>
                <img 
                  src={product.screenshots[0]} 
                  alt={`${product.name} Preview`} 
                  style={{ 
                    width: '100%', 
                    borderRadius: '16px', 
                    boxShadow: '0 25px 50px -12px rgba(0,0,0,0.25)', 
                    border: '1px solid #E2E8F0',
                    aspectRatio: '16/10',
                    objectFit: 'cover'
                  }} 
                />
              </div>
            ) : (
              <InteractiveAppWindow
                appName={product.name}
                modules={mockModules}
                color={product.color}
                compact={true}
              />
            )}
          </motion.div>
        </div>
      </div>
    </section>
  );
}
