import React, { useRef } from 'react';
import { motion, useInView } from 'framer-motion';
import { ArrowRight, Mail, Phone, MapPin } from 'lucide-react';

export default function Consultation() {
  const ref = useRef(null);
  const isInView = useInView(ref, { once: true, margin: '-80px' });

  return (
    <section id="consultation" ref={ref} className="section-dark" style={{ padding: '6rem 0' }}>
      <div className="container">
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '4rem', alignItems: 'center' }}>
          <motion.div
            initial={{ opacity: 0, x: -20 }}
            animate={isInView ? { opacity: 1, x: 0 } : {}}
            transition={{ duration: 0.6 }}
          >
            <h2 style={{ fontSize: '2.5rem', fontWeight: 800, marginBottom: '1rem', letterSpacing: '-0.02em' }}>
              Siap Memodernisasi
              <br />
              Operasional Anda?
            </h2>
            <p style={{ fontSize: '1.1rem', color: '#94A3B8', lineHeight: 1.7, marginBottom: '2.5rem', maxWidth: '480px' }}>
              Konsultasikan kebutuhan digital Anda dengan tim kami. Kami akan membantu mengidentifikasi solusi yang paling tepat untuk organisasi Anda.
            </p>

            <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', color: '#CBD5E1' }}>
                <Mail size={18} color="#0EA5E9" />
                <span style={{ fontSize: '0.9rem' }}>hello@moduvox.com</span>
              </div>
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', color: '#CBD5E1' }}>
                <Phone size={18} color="#0EA5E9" />
                <span style={{ fontSize: '0.9rem' }}>+62 21 1234 5678</span>
              </div>
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', color: '#CBD5E1' }}>
                <MapPin size={18} color="#0EA5E9" />
                <span style={{ fontSize: '0.9rem' }}>Jakarta, Indonesia</span>
              </div>
            </div>
          </motion.div>

          <motion.div
            initial={{ opacity: 0, x: 20 }}
            animate={isInView ? { opacity: 1, x: 0 } : {}}
            transition={{ duration: 0.6, delay: 0.2 }}
          >
            <div style={{
              background: 'rgba(255,255,255,0.05)',
              border: '1px solid rgba(255,255,255,0.1)',
              borderRadius: '16px', padding: '2rem',
            }}>
              <h3 style={{ fontSize: '1.25rem', fontWeight: 700, marginBottom: '1.5rem' }}>
                Jadwalkan Konsultasi
              </h3>
              <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
                <input placeholder="Nama Lengkap" style={{
                  padding: '0.75rem 1rem', borderRadius: '8px',
                  background: 'rgba(255,255,255,0.05)', border: '1px solid rgba(255,255,255,0.15)',
                  color: '#F8FAFC', fontSize: '0.9rem', outline: 'none',
                }} />
                <input placeholder="Email" type="email" style={{
                  padding: '0.75rem 1rem', borderRadius: '8px',
                  background: 'rgba(255,255,255,0.05)', border: '1px solid rgba(255,255,255,0.15)',
                  color: '#F8FAFC', fontSize: '0.9rem', outline: 'none',
                }} />
                <input placeholder="Perusahaan / Instansi" style={{
                  padding: '0.75rem 1rem', borderRadius: '8px',
                  background: 'rgba(255,255,255,0.05)', border: '1px solid rgba(255,255,255,0.15)',
                  color: '#F8FAFC', fontSize: '0.9rem', outline: 'none',
                }} />
                <select style={{
                  padding: '0.75rem 1rem', borderRadius: '8px',
                  background: 'rgba(255,255,255,0.05)', border: '1px solid rgba(255,255,255,0.15)',
                  color: '#94A3B8', fontSize: '0.9rem', outline: 'none',
                }}>
                  <option value="">Solusi yang Diminati</option>
                  <option value="hris">HRIS Enterprise</option>
                  <option value="siardi">SIARDI</option>
                  <option value="crm">CRM Solutions</option>
                  <option value="core-banking">Core Banking</option>
                  <option value="bantuan-potong">Bantuan Potong</option>
                  <option value="sinergi">Sinergi</option>
                  <option value="btn-channeling">BTN Channeling</option>
                </select>
                <button className="btn-primary" style={{
                  width: '100%', justifyContent: 'center',
                  padding: '0.875rem', fontSize: '1rem',
                  background: '#0EA5E9',
                }}>
                  Kirim Permintaan <ArrowRight size={18} />
                </button>
              </div>
            </div>
          </motion.div>
        </div>
      </div>
    </section>
  );
}
