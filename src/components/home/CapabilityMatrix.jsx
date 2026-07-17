import React, { useRef } from 'react';
import { motion, useInView } from 'framer-motion';
import {
  Users, FileArchive, BarChart3, CreditCard, Scissors, Network, Building,
  Smartphone, Globe, Workflow, Shield, Cpu, Database, Cloud, Code2
} from 'lucide-react';

export default function CapabilityMatrix() {
  const ref = useRef(null);
  const isInView = useInView(ref, { once: true, margin: '-80px' });

  const capabilities = [
    { label: 'HRIS & Payroll', icon: Users, desc: 'Manajemen SDM terintegrasi' },
    { label: 'CRM', icon: BarChart3, desc: 'Customer relationship management' },
    { label: 'Core Banking', icon: CreditCard, desc: 'Sistem inti perbankan' },
    { label: 'Channeling System', icon: Building, desc: 'Kredit channeling terintegrasi' },
    { label: 'Arsip Digital', icon: FileArchive, desc: 'Sistem arsip terstruktur' },
    { label: 'Workflow Automation', icon: Workflow, desc: 'Otomasi proses bisnis' },
    { label: 'Mobile Application', icon: Smartphone, desc: 'Native & cross-platform' },
    { label: 'API Integration', icon: Globe, desc: 'RESTful & real-time API' },
    { label: 'Banking Integration', icon: Shield, desc: 'OJK, BI, bank induk' },
    { label: 'Enterprise Architecture', icon: Cpu, desc: 'Scalable & maintainable' },
  ];

  const techStack = [
    'React', 'Node.js', 'PostgreSQL', 'Redis', 'Docker', 'AWS', 'REST API', 'gRPC',
  ];

  return (
    <section ref={ref} className="section" style={{ background: '#FFFFFF' }}>
      <div className="container">
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={isInView ? { opacity: 1, y: 0 } : {}}
          transition={{ duration: 0.5 }}
          style={{ textAlign: 'center', marginBottom: '4rem' }}
        >
          <h2 style={{ fontSize: '2.5rem', fontWeight: 800, color: '#1E293B', marginBottom: '1rem', letterSpacing: '-0.02em' }}>
            Kapabilitas Solusi
          </h2>
          <p style={{ fontSize: '1.1rem', color: '#64748B', maxWidth: '600px', margin: '0 auto' }}>
            Cakupan solusi enterprise yang telah kami kuasai dan operasikan di lingkungan produksi.
          </p>
        </motion.div>

        <div style={{
          display: 'grid', gridTemplateColumns: 'repeat(5, 1fr)',
          gap: '1rem', marginBottom: '4rem',
        }}>
          {capabilities.map((cap, i) => {
            const Icon = cap.icon;
            return (
              <motion.div
                key={i}
                initial={{ opacity: 0, y: 15 }}
                animate={isInView ? { opacity: 1, y: 0 } : {}}
                transition={{ duration: 0.4, delay: i * 0.05 }}
                style={{
                  padding: '1.5rem 1rem', textAlign: 'center',
                  background: '#F8FAFC', borderRadius: '12px',
                  border: '1px solid #E2E8F0',
                  transition: 'all 200ms ease',
                  cursor: 'default',
                }}
                onMouseEnter={e => {
                  e.currentTarget.style.borderColor = '#005BAC';
                  e.currentTarget.style.boxShadow = '0 4px 12px rgba(0,91,172,0.08)';
                  e.currentTarget.style.transform = 'translateY(-2px)';
                }}
                onMouseLeave={e => {
                  e.currentTarget.style.borderColor = '#E2E8F0';
                  e.currentTarget.style.boxShadow = 'none';
                  e.currentTarget.style.transform = 'translateY(0)';
                }}
              >
                <Icon size={28} color="#005BAC" style={{ marginBottom: '0.75rem' }} />
                <div style={{ fontSize: '0.875rem', fontWeight: 600, color: '#1E293B', marginBottom: '0.25rem' }}>{cap.label}</div>
                <div style={{ fontSize: '0.75rem', color: '#94A3B8' }}>{cap.desc}</div>
              </motion.div>
            );
          })}
        </div>

        {/* Tech stack - secondary */}
        <motion.div
          initial={{ opacity: 0, y: 10 }}
          animate={isInView ? { opacity: 1, y: 0 } : {}}
          transition={{ duration: 0.5, delay: 0.5 }}
          style={{ textAlign: 'center' }}
        >
          <div style={{ fontSize: '0.75rem', fontWeight: 600, color: '#94A3B8', textTransform: 'uppercase', letterSpacing: '0.1em', marginBottom: '1rem' }}>
            Technology Stack
          </div>
          <div style={{ display: 'flex', flexWrap: 'wrap', justifyContent: 'center', gap: '0.5rem' }}>
            {techStack.map((tech, i) => (
              <span key={i} style={{
                padding: '0.375rem 0.875rem', borderRadius: '999px',
                background: '#F1F5F9', color: '#64748B',
                fontSize: '0.8rem', fontWeight: 500,
                border: '1px solid #E2E8F0',
              }}>{tech}</span>
            ))}
          </div>
        </motion.div>
      </div>
    </section>
  );
}
