import React, { useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { motion } from 'framer-motion';
import { ArrowLeft, CheckCircle2, ArrowRight } from 'lucide-react';
import { products } from '../data/productData';
import InteractiveAppWindow from '../components/showcase/InteractiveAppWindow';
import RoleSwitcher from '../components/showcase/RoleSwitcher';

export default function SolutionDetail() {
  const { productId } = useParams();
  const product = products.find(p => p.id === productId);
  const [activeRole, setActiveRole] = useState(product?.roles[0]?.id || '');

  if (!product) {
    return (
      <div className="container" style={{ padding: '6rem 0', textAlign: 'center' }}>
        <h2>Produk tidak ditemukan</h2>
        <Link to="/" className="btn-primary" style={{ marginTop: '2rem' }}>Kembali ke Beranda</Link>
      </div>
    );
  }

  const Icon = product.icon;
  const currentModules = product.modules[activeRole] || [];

  const workflowSteps = product.benefits.map((b, i) => ({
    step: i + 1,
    label: b,
  }));

  return (
    <div>
      {/* Hero */}
      <section style={{ padding: '4rem 0 3rem 0', background: '#FFFFFF', borderBottom: '1px solid #E2E8F0' }}>
        <div className="container">
          <Link to="/" style={{ display: 'inline-flex', alignItems: 'center', gap: '0.5rem', color: '#64748B', fontSize: '0.875rem', marginBottom: '2rem', transition: 'color 150ms' }}
            onMouseEnter={e => e.currentTarget.style.color = '#005BAC'}
            onMouseLeave={e => e.currentTarget.style.color = '#64748B'}
          >
            <ArrowLeft size={16} /> Kembali ke Experience Center
          </Link>

          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5 }}
          >
            <div style={{
              display: 'inline-flex', alignItems: 'center', gap: '0.5rem',
              padding: '0.375rem 0.75rem',
              background: `${product.color}10`,
              border: `1px solid ${product.color}25`,
              borderRadius: '999px', marginBottom: '1.5rem',
            }}>
              <Icon size={16} color={product.color} />
              <span style={{ fontSize: '0.8rem', fontWeight: 600, color: product.color }}>{product.name}</span>
            </div>

            <h1 style={{ fontSize: '3rem', fontWeight: 800, color: '#1E293B', marginBottom: '1rem', letterSpacing: '-0.03em' }}>
              {product.tagline}
            </h1>
            <p style={{ fontSize: '1.15rem', color: '#64748B', maxWidth: '700px', lineHeight: 1.7 }}>
              {product.problem}
            </p>
          </motion.div>
        </div>
      </section>

      {/* Interactive Demo */}
      <section style={{ padding: '4rem 0', background: '#F8FAFC' }}>
        <div className="container-wide">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: 0.2 }}
          >
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '2rem' }}>
              <div>
                <h2 style={{ fontSize: '1.5rem', fontWeight: 700, color: '#1E293B', marginBottom: '0.5rem' }}>
                  Interactive Demo
                </h2>
                <p style={{ color: '#64748B', fontSize: '0.9rem' }}>
                  Klik menu di sidebar untuk menjelajahi modul-modul yang tersedia.
                </p>
              </div>
              <RoleSwitcher
                roles={product.roles}
                activeRole={activeRole}
                onRoleChange={setActiveRole}
                color={product.color}
              />
            </div>

            <InteractiveAppWindow
              appName={`${product.name} — ${product.roles.find(r => r.id === activeRole)?.label}`}
              modules={currentModules}
              color={product.color}
            />
          </motion.div>
        </div>
      </section>

      {/* Benefits */}
      <section style={{ padding: '4rem 0', background: '#FFFFFF' }}>
        <div className="container">
          <h2 style={{ fontSize: '2rem', fontWeight: 800, color: '#1E293B', marginBottom: '2rem', letterSpacing: '-0.02em' }}>
            Keunggulan Utama
          </h2>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: '1.5rem' }}>
            {product.benefits.map((benefit, i) => (
              <motion.div
                key={i}
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.4, delay: 0.1 + i * 0.1 }}
                style={{
                  display: 'flex', alignItems: 'flex-start', gap: '1rem',
                  padding: '1.5rem', background: '#F8FAFC',
                  borderRadius: '12px', border: '1px solid #E2E8F0',
                }}
              >
                <CheckCircle2 size={22} color={product.color} style={{ flexShrink: 0, marginTop: 2 }} />
                <span style={{ fontSize: '0.95rem', color: '#334155', lineHeight: 1.6 }}>{benefit}</span>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA */}
      <section style={{ padding: '4rem 0', background: '#F8FAFC', borderTop: '1px solid #E2E8F0' }}>
        <div className="container" style={{ textAlign: 'center' }}>
          <h2 style={{ fontSize: '2rem', fontWeight: 800, color: '#1E293B', marginBottom: '1rem' }}>
            Tertarik dengan {product.name}?
          </h2>
          <p style={{ color: '#64748B', marginBottom: '2rem', fontSize: '1.1rem' }}>
            Hubungi kami untuk demo lengkap dan diskusi kebutuhan spesifik organisasi Anda.
          </p>
          <div style={{ display: 'flex', gap: '1rem', justifyContent: 'center' }}>
            <Link to="/portfolio" className="btn-primary" style={{ padding: '0.875rem 2rem', fontSize: '1rem' }}>
              Minta Demo Lengkap <ArrowRight size={18} />
            </Link>
            <Link to="/" className="btn-outline" style={{ padding: '0.875rem 2rem', fontSize: '1rem' }}>
              Lihat Produk Lainnya
            </Link>
          </div>
        </div>
      </section>
    </div>
  );
}
