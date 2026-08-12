import React from 'react';
import { motion } from 'framer-motion';
import { Code2, Blocks, ArrowRightLeft, ArrowRight } from 'lucide-react';

export default function WhatWeBuild() {
  const capabilities = [
    {
      id: 'custom',
      icon: <Code2 size={24} color="var(--primary)" />,
      title: 'Custom Enterprise Applications',
      desc: 'Sistem yang dibangun berdasarkan proses bisnis, workflow, dan kebutuhan operasional spesifik organisasi Anda.'
    },
    {
      id: 'solutions',
      icon: <Blocks size={24} color="#10B981" />,
      title: 'Enterprise Solutions',
      desc: 'Solusi perangkat lunak siap implementasi untuk kebutuhan bisnis yang membutuhkan deployment lebih cepat namun scalable.'
    },
    {
      id: 'integration',
      icon: <ArrowRightLeft size={24} color="#F59E0B" />,
      title: 'System Integration',
      desc: 'Integrasi antar sistem, API, database, dan platform eksisting untuk membangun ekosistem digital yang terhubung tanpa batas.'
    }
  ];

  return (
    <section id="what-we-build" style={{ padding: '6rem 0', background: '#FFFFFF' }}>
      <div className="container">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6 }}
          style={{ textAlign: 'center', marginBottom: '4rem', maxWidth: '800px', margin: '0 auto 4rem auto' }}
        >
          <div className="section-eyebrow">
            <span className="section-eyebrow-dot" />
            Core Capabilities
          </div>
          <h2 className="section-title" style={{ fontSize: '2.5rem', marginBottom: '1.25rem' }}>
            What We Build
          </h2>
          <p className="section-desc" style={{ fontSize: '1.2rem' }}>
            Enterprise systems designed around the way your organization operates. Dari pengembangan sistem kustom hingga integrasi infrastruktur kompleks.
          </p>
        </motion.div>

        <div style={{
          display: 'grid',
          gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))',
          gap: '2rem',
          marginBottom: '4rem'
        }}>
          {capabilities.map((cap, i) => (
            <motion.div
              key={cap.id}
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ duration: 0.5, delay: i * 0.1 }}
              className="glass-card"
              style={{
                padding: '2.5rem',
                background: '#F8FAFC',
                borderRadius: '1.5rem',
                border: '1px solid var(--border)',
                display: 'flex',
                flexDirection: 'column',
                gap: '1.5rem'
              }}
            >
              <div style={{
                width: '56px', height: '56px',
                background: '#FFFFFF',
                borderRadius: '1rem',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                boxShadow: '0 4px 12px rgba(0,0,0,0.05)'
              }}>
                {cap.icon}
              </div>
              <div>
                <h3 style={{ fontSize: '1.25rem', fontWeight: 700, marginBottom: '0.75rem', color: 'var(--foreground)' }}>
                  {cap.title}
                </h3>
                <p style={{ color: 'var(--text-muted)', lineHeight: 1.6 }}>
                  {cap.desc}
                </p>
              </div>
            </motion.div>
          ))}
        </div>

        <motion.div
          initial={{ opacity: 0 }}
          whileInView={{ opacity: 1 }}
          viewport={{ once: true }}
          style={{
            textAlign: 'center',
            padding: '2rem',
            background: 'linear-gradient(135deg, rgba(0,91,172,0.05), rgba(14,165,233,0.05))',
            borderRadius: '1rem',
            border: '1px solid var(--primary-light)'
          }}
        >
          <p style={{ fontSize: '1.1rem', fontWeight: 600, color: 'var(--foreground)', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '0.5rem', margin: 0 }}>
            Don't see what you need? <span style={{ color: 'var(--primary)' }}>We build it custom.</span>
            <a href="/#consultation" style={{ display: 'flex', alignItems: 'center', color: 'var(--primary)', textDecoration: 'none', marginLeft: '0.5rem' }}>
              Diskusi dengan tim teknis kami <ArrowRight size={18} />
            </a>
          </p>
        </motion.div>
      </div>
    </section>
  );
}
