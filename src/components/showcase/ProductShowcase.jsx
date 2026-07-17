import React, { useState } from 'react';
import { motion, useInView } from 'framer-motion';
import { useRef } from 'react';
import { Link } from 'react-router-dom';
import { ArrowRight, CheckCircle2 } from 'lucide-react';
import InteractiveAppWindow from './InteractiveAppWindow';
import RoleSwitcher from './RoleSwitcher';

export default function ProductShowcase({ product, index }) {
  const isEven = index % 2 === 0;
  const [activeRole, setActiveRole] = useState(product.roles[0].id);
  const ref = useRef(null);
  const isInView = useInView(ref, { once: true, margin: '-100px' });

  const currentModules = product.modules[activeRole] || [];
  const Icon = product.icon;

  // Alternate between soft-tinted backgrounds for visual rhythm
  const bgColors = [
    '#F1F5F9',   // Slate-100
    '#FFFFFF',   // White
  ];
  const bgColor = bgColors[index % 2];

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
                {product.name}
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
              {product.problem}
            </p>

            <ul style={{ listStyle: 'none', padding: 0, marginBottom: '2rem' }}>
              {product.benefits.map((benefit, i) => (
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
                  {benefit}
                </motion.li>
              ))}
            </ul>

            {/* Role Switcher */}
            <div style={{ marginBottom: '1.5rem' }}>
              <div style={{ fontSize: '0.75rem', fontWeight: 600, color: '#94A3B8', textTransform: 'uppercase', letterSpacing: '0.08em', marginBottom: '0.75rem' }}>
                Lihat sebagai
              </div>
              <RoleSwitcher
                roles={product.roles}
                activeRole={activeRole}
                onRoleChange={setActiveRole}
                color={product.color}
              />
            </div>

            <Link
              to="/portfolio"
              style={{
                display: 'inline-flex', alignItems: 'center', gap: '0.5rem',
                color: product.color, fontWeight: 600, fontSize: '0.9rem',
                transition: 'gap 200ms ease',
              }}
              onMouseEnter={e => e.currentTarget.style.gap = '0.75rem'}
              onMouseLeave={e => e.currentTarget.style.gap = '0.5rem'}
            >
              Lihat Demo Lengkap <ArrowRight size={18} />
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
            {product.id === 'hris' ? (
              <div style={{ position: 'relative', width: '100%', paddingBottom: '10%' }}>
                <img 
                  src="/images/hris_admin_mockup.png" 
                  alt="HRIS Admin Dashboard" 
                  style={{ 
                    width: '90%', 
                    borderRadius: '16px', 
                    boxShadow: '0 25px 50px -12px rgba(0,0,0,0.25)', 
                    border: '1px solid #E2E8F0' 
                  }} 
                />
                <img 
                  src="/images/hris_mobile_mockup.png" 
                  alt="HRIS Mobile App" 
                  style={{ 
                    position: 'absolute', 
                    bottom: '-15%', 
                    right: '-5%', 
                    width: '35%', 
                    borderRadius: '24px', 
                    boxShadow: '0 25px 50px -12px rgba(0,0,0,0.5)',
                    border: '4px solid #fff'
                  }} 
                />
              </div>
            ) : (
              <InteractiveAppWindow
                appName={product.name}
                modules={currentModules}
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
