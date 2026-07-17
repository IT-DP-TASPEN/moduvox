import React, { useRef } from 'react';
import { motion, useInView } from 'framer-motion';
import { Search, PenTool, Code2, Rocket, HeadphonesIcon } from 'lucide-react';

export default function Methodology() {
  const ref = useRef(null);
  const isInView = useInView(ref, { once: true, margin: '-80px' });

  const steps = [
    { icon: Search, title: 'Discovery', desc: 'Analisis kebutuhan bisnis, identifikasi pain points, dan pemetaan proses existing.' },
    { icon: PenTool, title: 'Design', desc: 'Perancangan arsitektur sistem, UX/UI, dan prototype interaktif untuk validasi.' },
    { icon: Code2, title: 'Development', desc: 'Pengembangan iteratif dengan sprint review, testing otomatis, dan code review.' },
    { icon: Rocket, title: 'Deployment', desc: 'Go-live bertahap dengan migrasi data, training user, dan monitoring intensif.' },
    { icon: HeadphonesIcon, title: 'Support', desc: 'Maintenance berkala, pembaruan fitur, dan dukungan teknis berkelanjutan.' },
  ];

  return (
    <section ref={ref} className="section" style={{ background: '#F8FAFC' }}>
      <div className="container">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={isInView ? { opacity: 1, y: 0 } : {}}
          transition={{ duration: 0.5 }}
          style={{ textAlign: 'center', marginBottom: '4rem' }}
        >
          {/* Eyebrow */}
          <div style={{ display: 'flex', justifyContent: 'center', marginBottom: '1rem' }}>
            <div className="section-eyebrow">
              <span className="section-eyebrow-dot" />
              Cara Kami Bekerja
            </div>
          </div>

          <h2 style={{ fontSize: '2.5rem', fontWeight: 800, color: '#1E293B', marginBottom: '1rem', letterSpacing: '-0.02em' }}>
            Metodologi Implementasi
          </h2>
          <p style={{ fontSize: '1.1rem', color: '#64748B', maxWidth: '600px', margin: '0 auto' }}>
            Pendekatan terstruktur untuk memastikan setiap implementasi berjalan tepat waktu dan sesuai ekspektasi.
          </p>
        </motion.div>

        <div style={{
          display: 'flex', gap: '0', alignItems: 'flex-start',
          position: 'relative',
        }}>
          {/* Connecting line */}
          <div style={{
            position: 'absolute', top: '32px', left: '10%', right: '10%',
            height: '2px',
            background: 'linear-gradient(90deg, var(--primary-light), var(--primary), var(--secondary), var(--primary), var(--primary-light))',
            zIndex: 0,
          }} />

          {steps.map((step, i) => {
            const Icon = step.icon;
            return (
              <motion.div
                key={i}
                initial={{ opacity: 0, y: 20 }}
                animate={isInView ? { opacity: 1, y: 0 } : {}}
                transition={{ duration: 0.4, delay: 0.1 + i * 0.12 }}
                style={{
                  flex: 1, textAlign: 'center', padding: '0 0.75rem',
                  position: 'relative', zIndex: 1,
                }}
              >
                <div style={{
                  width: '64px', height: '64px', borderRadius: '50%',
                  background: '#FFFFFF', border: '2px solid #005BAC',
                  display: 'flex', alignItems: 'center', justifyContent: 'center',
                  margin: '0 auto 1.25rem auto',
                  boxShadow: '0 4px 16px rgba(0,91,172,0.12)',
                  transition: 'all 300ms ease',
                }}
                onMouseEnter={e => {
                  e.currentTarget.style.background = 'var(--primary)';
                  e.currentTarget.querySelector('svg').style.color = '#fff';
                }}
                onMouseLeave={e => {
                  e.currentTarget.style.background = '#FFFFFF';
                  e.currentTarget.querySelector('svg').style.color = '#005BAC';
                }}
                >
                  <Icon size={24} color="#005BAC" style={{ transition: 'color 300ms ease' }} />
                </div>
                <div style={{
                  fontSize: '0.7rem', fontWeight: 700, color: '#005BAC',
                  textTransform: 'uppercase', letterSpacing: '0.08em', marginBottom: '0.5rem',
                }}>
                  Step {i + 1}
                </div>
                <h3 style={{ fontSize: '1.125rem', fontWeight: 700, color: '#1E293B', marginBottom: '0.5rem' }}>
                  {step.title}
                </h3>
                <p style={{ fontSize: '0.8rem', color: '#64748B', lineHeight: 1.6 }}>
                  {step.desc}
                </p>
              </motion.div>
            );
          })}
        </div>
      </div>
    </section>
  );
}
