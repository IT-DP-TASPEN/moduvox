import React from 'react';
import { motion } from 'framer-motion';
import { Building2, Code2, Users, Rocket, Mail, MapPin } from 'lucide-react';
import styles from './Portfolio.module.css'; // Reusing some base styles for consistency

export default function About() {
  return (
    <div style={{ padding: '8rem 0', background: '#FFFFFF' }}>
      <div className="container">
        {/* Header */}
        <motion.section
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
          style={{ textAlign: 'center', marginBottom: '6rem', maxWidth: '800px', margin: '0 auto 6rem auto' }}
        >
          <div className="section-eyebrow" style={{ display: 'inline-flex', marginBottom: '1rem' }}>
            <span className="section-eyebrow-dot" />
            Company Profile
          </div>
          <h1 style={{ fontSize: '3.5rem', fontWeight: 800, color: 'var(--foreground)', marginBottom: '1.5rem', letterSpacing: '-0.03em' }}>
            PT Moduvox Tech ID
          </h1>
          <p style={{ fontSize: '1.25rem', color: 'var(--text-muted)', lineHeight: 1.7 }}>
            Kami adalah perusahaan teknologi yang berfokus pada pengembangan perangkat lunak enterprise dan ekosistem digital untuk organisasi dengan kebutuhan operasional yang kompleks.
          </p>
        </motion.section>

        {/* Two Column Layout: Who We Are & What We Do */}
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))', gap: '4rem', marginBottom: '6rem' }}>
          <motion.div
            initial={{ opacity: 0, x: -20 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
            style={{ padding: '2.5rem', background: '#F8FAFC', borderRadius: '1.5rem', border: '1px solid var(--border)' }}
          >
            <Building2 size={32} color="var(--primary)" style={{ marginBottom: '1.5rem' }} />
            <h2 style={{ fontSize: '1.75rem', fontWeight: 700, color: 'var(--foreground)', marginBottom: '1rem' }}>Who We Are</h2>
            <p style={{ color: 'var(--text-muted)', lineHeight: 1.7 }}>
              Berbasis di Indonesia, tim kami terdiri dari software engineers, system architects, dan business analysts berpengalaman yang berdedikasi membangun fondasi digital yang tangguh untuk bisnis Anda. Kami bukan sekadar vendor, melainkan mitra strategis dalam perjalanan transformasi digital Anda.
            </p>
          </motion.div>
          
          <motion.div
            initial={{ opacity: 0, x: 20 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
            style={{ padding: '2.5rem', background: '#F8FAFC', borderRadius: '1.5rem', border: '1px solid var(--border)' }}
          >
            <Code2 size={32} color="var(--primary)" style={{ marginBottom: '1.5rem' }} />
            <h2 style={{ fontSize: '1.75rem', fontWeight: 700, color: 'var(--foreground)', marginBottom: '1rem' }}>What We Do</h2>
            <p style={{ color: 'var(--text-muted)', lineHeight: 1.7 }}>
              Kami membangun core banking systems, HRIS, CRM, dan sistem manajemen dokumen yang mampu menangani jutaan transaksi. Dari proses penemuan kebutuhan, desain arsitektur, hingga deployment operasional penuh.
            </p>
          </motion.div>
        </div>

        {/* Expertise Section */}
        <motion.section
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          style={{ marginBottom: '6rem' }}
        >
          <div style={{ textAlign: 'center', marginBottom: '3rem' }}>
            <h2 style={{ fontSize: '2.5rem', fontWeight: 800, color: 'var(--foreground)' }}>Our Expertise</h2>
          </div>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))', gap: '2rem' }}>
            {[
              { icon: <Users size={24} />, title: 'Enterprise Solutions', desc: 'Sistem siap pakai yang didesain untuk kebutuhan standar industri.' },
              { icon: <Code2 size={24} />, title: 'Custom Development', desc: 'Pembuatan software kustom yang selaras 100% dengan proses bisnis unik Anda.' },
              { icon: <Rocket size={24} />, title: 'System Integration', desc: 'Integrasi platform eksisting ke dalam satu ekosistem yang kohesif dan aman.' }
            ].map((item, i) => (
              <div key={i} style={{ padding: '2rem', border: '1px solid var(--border)', borderRadius: '1rem', background: '#FFFFFF' }}>
                <div style={{ width: '48px', height: '48px', background: '#F0F9FF', borderRadius: '0.75rem', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'var(--primary)', marginBottom: '1.5rem' }}>
                  {item.icon}
                </div>
                <h3 style={{ fontSize: '1.25rem', fontWeight: 700, marginBottom: '0.75rem' }}>{item.title}</h3>
                <p style={{ color: 'var(--text-muted)', lineHeight: 1.6 }}>{item.desc}</p>
              </div>
            ))}
          </div>
        </motion.section>

        {/* Company Info */}
        <motion.section
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          style={{ padding: '4rem', background: 'var(--foreground)', color: '#FFFFFF', borderRadius: '1.5rem', display: 'flex', flexWrap: 'wrap', gap: '4rem', justifyContent: 'space-between' }}
        >
          <div style={{ flex: '1 1 300px' }}>
            <h2 style={{ fontSize: '2rem', fontWeight: 800, marginBottom: '1rem', color: '#FFFFFF' }}>Company Information</h2>
            <p style={{ color: '#94A3B8', lineHeight: 1.7, marginBottom: '2rem' }}>
              Silakan hubungi kami untuk mendiskusikan kebutuhan sistem operasional dan transformasi digital perusahaan Anda.
            </p>
          </div>
          <div style={{ flex: '1 1 300px', display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
            <div style={{ display: 'flex', alignItems: 'flex-start', gap: '1rem' }}>
              <Building2 size={24} color="#94A3B8" />
              <div>
                <strong style={{ display: 'block', color: '#FFFFFF', marginBottom: '0.25rem' }}>Entity</strong>
                <span style={{ color: '#CBD5E1' }}>PT Moduvox Tech ID</span>
              </div>
            </div>
            <div style={{ display: 'flex', alignItems: 'flex-start', gap: '1rem' }}>
              <Mail size={24} color="#94A3B8" />
              <div>
                <strong style={{ display: 'block', color: '#FFFFFF', marginBottom: '0.25rem' }}>Email</strong>
                <a href="mailto:Moduvox.tech@gmail.com" style={{ color: 'var(--primary-light)', textDecoration: 'none' }}>Moduvox.tech@gmail.com</a>
              </div>
            </div>
            <div style={{ display: 'flex', alignItems: 'flex-start', gap: '1rem' }}>
              <MapPin size={24} color="#94A3B8" />
              <div>
                <strong style={{ display: 'block', color: '#FFFFFF', marginBottom: '0.25rem' }}>Location</strong>
                <span style={{ color: '#CBD5E1', lineHeight: 1.6 }}>
                  Jakarta, Indonesia<br />
                  Melayani klien secara nasional dan regional.
                </span>
              </div>
            </div>
          </div>
        </motion.section>
      </div>
    </div>
  );
}
