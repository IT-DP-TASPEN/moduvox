import React, { useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { motion } from 'framer-motion';
import { 
  ArrowLeft, ExternalLink, CheckCircle2,
  Layers, Layout, Server, Code2, 
  ArrowRight, ShieldCheck, RefreshCw, Zap
} from 'lucide-react';
import { products } from '../data/productData';
import s from './SolutionDetail.module.css';

export default function SolutionDetail() {
  const { productId } = useParams();
  const product = products.find(p => p.id === productId);

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [productId]);

  if (!product) {
    return (
      <div style={{ minHeight: '100vh', display: 'flex', alignItems: 'center', justifyContent: 'center', background: '#F8FAFC' }}>
        <div style={{ textAlign: 'center' }}>
          <h2 style={{ fontSize: 28, fontWeight: 700, color: '#1E293B', marginBottom: 16 }}>Aplikasi tidak ditemukan</h2>
          <Link to="/portfolio" style={{ display: 'inline-flex', alignItems: 'center', gap: 8, padding: '12px 24px', background: '#005BAC', color: '#fff', borderRadius: 999, fontWeight: 600, textDecoration: 'none' }}>
            <ArrowLeft size={18} /> Kembali ke Portofolio
          </Link>
        </div>
      </div>
    );
  }

  const Icon = product.icon;
  const fadeIn = { hidden: { opacity: 0, y: 20 }, visible: { opacity: 1, y: 0 } };

  return (
    <div className={s.page}>

      {/* HERO */}
      <section className={s.hero}>
        <div className={s.heroGlow} style={{ backgroundColor: product.color }} />
        <div className={s.heroContainer}>
          <Link to="/portfolio" className={s.heroBack}>
            <ArrowLeft size={16} /> Kembali ke Portofolio
          </Link>
          <div className={s.heroGrid}>
            {/* Left */}
            <motion.div 
              className={s.heroContent}
              initial={{ opacity: 0, y: 20 }} 
              animate={{ opacity: 1, y: 0 }} 
              transition={{ duration: 0.6, ease: [0.25, 0.46, 0.45, 0.94] }}
            >
              <div className={s.heroBadge} style={{ backgroundColor: `${product.color}10`, border: `1px solid ${product.color}20`, color: product.color }}>
                <Icon size={14} strokeWidth={2.5} />
                {product.category || 'Enterprise System'}
              </div>
              <h1 className={s.heroTitle}>{product.tagline}</h1>
              <p className={s.heroDesc}>
                {product.longDescription.split('.').slice(0, 2).join('.')}.
              </p>
              <div className={s.heroCta}>
                <a href={product.demoUrl} target="_blank" rel="noopener noreferrer" className={s.ctaPrimary} style={{ backgroundColor: product.color, boxShadow: `0 10px 30px -8px ${product.color}50` }}>
                  Lihat Demo <ExternalLink size={17} />
                </a>
                <a href="/#consultation" className={s.ctaSecondary}>
                  Konsultasi <ArrowRight size={17} />
                </a>
              </div>
              <div className={s.heroChecks}>
                {product.highlights?.slice(0, 3).map((h, i) => (
                  <div key={i} className={s.heroCheck}>
                    <CheckCircle2 size={16} style={{ color: product.color }} />
                    {h.title}
                  </div>
                ))}
              </div>
            </motion.div>

            {/* Right — Product Preview */}
            <motion.div 
              className={s.heroPreview}
              initial={{ opacity: 0, y: 20, scale: 0.97 }} 
              animate={{ opacity: 1, y: 0, scale: 1 }} 
              transition={{ duration: 0.7, delay: 0.15, ease: [0.25, 0.46, 0.45, 0.94] }}
            >
              <div className={s.previewFloat}>
                <div className={s.previewFrame}>
                  {product.screenshots?.[0] ? (
                    <img src={product.screenshots[0]} alt={`${product.name} Preview`} className={s.previewImg} />
                  ) : (
                    <div className={s.previewPlaceholder}>
                      <Layout size={56} style={{ opacity: 0.2, marginBottom: 12 }} />
                      <span style={{ fontWeight: 500 }}>Dashboard Preview</span>
                    </div>
                  )}
                </div>
                <div className={s.statusBadge}>
                  <div className={s.statusIcon} style={{ backgroundColor: product.color }}>
                    <ShieldCheck size={17} />
                  </div>
                  <div>
                    <div className={s.statusLabel}>System Status</div>
                    <div className={s.statusValue}>Secure & Active</div>
                  </div>
                  <div className={s.statusDot} />
                </div>
              </div>
            </motion.div>
          </div>
        </div>
      </section>

      {/* HIGHLIGHTS */}
      {product.highlights && (
        <section className={s.highlights}>
          <div className={s.highlightsContainer}>
            <div className={s.highlightsGrid}>
              {product.highlights.map((item, idx) => {
                const HIcon = item.icon;
                return (
                  <motion.div key={idx} className={s.highlightCard} initial="hidden" whileInView="visible" viewport={{ once: true }} variants={fadeIn} transition={{ delay: idx * 0.08 }}>
                    <div className={s.highlightIcon} style={{ backgroundColor: `${product.color}12`, color: product.color }}>
                      <HIcon size={22} />
                    </div>
                    <div className={s.highlightTitle}>{item.title}</div>
                    <div className={s.highlightDesc}>{item.desc}</div>
                  </motion.div>
                );
              })}
            </div>
          </div>
        </section>
      )}

      {/* OVERVIEW */}
      <section className={s.section}>
        <div className={s.container}>
          <div className={s.overviewGrid}>
            <motion.div initial="hidden" whileInView="visible" viewport={{ once: true }} variants={fadeIn}>
              <h2 className={s.overviewTitle}>Solusi Enterprise Terintegrasi</h2>
              <div className={s.overviewText}>
                <p>{product.longDescription}</p>
                <p>Arsitektur modern dan skalabel memastikan performa tinggi dan keamanan data yang terjamin untuk operasional bisnis skala menengah hingga besar.</p>
              </div>
            </motion.div>
            <motion.div initial="hidden" whileInView="visible" viewport={{ once: true }} variants={fadeIn} transition={{ delay: 0.15 }}>
              <div className={s.specsCard}>
                <div className={s.specsTitle}>Spesifikasi Sistem</div>
                <div className={s.specRow}>
                  <span className={s.specLabel}>Technology</span>
                  <span className={s.specValue}><Code2 size={15} color="#3B82F6" /> {product.techStackList?.[0]?.tech || 'Laravel'}</span>
                </div>
                <div className={s.specRow}>
                  <span className={s.specLabel}>Category</span>
                  <span className={s.specValue}><Layers size={15} color="#8B5CF6" /> {product.category}</span>
                </div>
                <div className={s.specRow}>
                  <span className={s.specLabel}>Deployment</span>
                  <span className={s.specValue}><Server size={15} color="#10B981" /> Web Based / Cloud</span>
                </div>
                <div className={s.specRow}>
                  <span className={s.specLabel}>Integration</span>
                  <span className={s.specValue}><Zap size={15} color="#F59E0B" /> API Ready</span>
                </div>
              </div>
            </motion.div>
          </div>
        </div>
      </section>

      {/* MODULES */}
      <section className={s.sectionWhite}>
        <div className={s.container}>
          <div className={s.sectionHeader}>
            <h2 className={s.sectionTitle}>Modul & Kapabilitas Utama</h2>
            <p className={s.sectionSubtitle}>Sistem dirancang modular untuk menangani kebutuhan spesifik operasional bisnis dengan efisiensi tinggi.</p>
          </div>
          <div className={s.modulesGrid}>
            {product.modulesDetail.map((mod, idx) => {
              const MIcon = mod.icon || Layout;
              return (
                <motion.div key={idx} className={s.moduleCard} initial="hidden" whileInView="visible" viewport={{ once: true, margin: "-40px" }} variants={fadeIn} transition={{ delay: (idx % 3) * 0.08 }}>
                  <div className={s.moduleIcon} style={{ color: product.color }}>
                    <MIcon size={26} strokeWidth={2} />
                  </div>
                  <div className={s.moduleTitle}>{mod.title}</div>
                  <p className={s.moduleDesc}>{mod.shortDesc}</p>
                  <ul className={s.moduleFeatures}>
                    {mod.features.map((feat, fi) => (
                      <li key={fi} className={s.moduleFeature}>
                        <CheckCircle2 size={15} style={{ color: product.color, opacity: 0.7 }} />
                        <span>{feat}</span>
                      </li>
                    ))}
                  </ul>
                  <div className={s.moduleExplore} style={{ color: product.color }}>
                    Explore module <ArrowRight size={15} />
                  </div>
                </motion.div>
              );
            })}
          </div>
        </div>
      </section>

      {/* INTEGRATION */}
      <section className={s.integration}>
        <div className={s.container}>
          <div className={s.integrationInner}>
            <div className={s.integrationGlow} style={{ backgroundColor: product.color }} />
            <div className={s.integrationGrid}>
              <motion.div initial="hidden" whileInView="visible" viewport={{ once: true }} variants={fadeIn}>
                <div className={s.integBadge}>
                  <Zap size={13} style={{ color: product.color }} /> Seamless Integration
                </div>
                <h2 className={s.integTitle}>
                  {product.id === 'inventaris' ? 'Terintegrasi dengan Core Banking' : 'Ekosistem Digital Terhubung'}
                </h2>
                <p className={s.integDesc}>
                  {product.id === 'inventaris' 
                    ? 'Jurnal penyusutan dapat dikirim langsung ke Core Banking melalui API General Ledger sehingga proses pencatatan menjadi lebih terkontrol dan mengurangi proses manual.' 
                    : 'Sistem dibangun dengan arsitektur API-Ready yang memungkinkan pertukaran data secara aman dan real-time dengan aplikasi pihak ketiga atau sistem internal lainnya.'}
                </p>
                <ul className={s.integFeatures}>
                  {['Automatic journal generation / Data Sync', 'Secure API transmission', 'Success / Failed monitoring', 'Automatic retry mechanism'].map((f, i) => (
                    <li key={i} className={s.integFeature}>
                      <div className={s.integFeatureIcon}>
                        <CheckCircle2 size={13} style={{ color: product.color }} />
                      </div>
                      {f}
                    </li>
                  ))}
                </ul>
              </motion.div>
              <motion.div initial={{ opacity: 0, scale: 0.95 }} whileInView={{ opacity: 1, scale: 1 }} viewport={{ once: true }} transition={{ duration: 0.5 }}>
                <div className={s.diagram}>
                  <div className={s.diagramNode}>
                    <h4>{product.name}</h4>
                    <span>Data Source</span>
                  </div>
                  <div className={s.diagramConnector}>
                    <div className={s.diagramPulse} style={{ backgroundColor: product.color }} />
                  </div>
                  <div className={s.diagramNode}>
                    <h4 style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 8 }}>
                      <RefreshCw size={14} style={{ color: product.color }} /> API Gateway
                    </h4>
                    <span>JSON / REST API</span>
                  </div>
                  <div className={s.diagramConnector}>
                    <div className={s.diagramPulse} style={{ backgroundColor: product.color }} />
                  </div>
                  <div className={s.diagramNode}>
                    <h4>{product.id === 'inventaris' ? 'Core Banking / GL' : 'External System'}</h4>
                    <span>Destination</span>
                  </div>
                </div>
              </motion.div>
            </div>
          </div>
        </div>
      </section>

      {/* WORKFLOW */}
      {product.workflow && (
        <section className={s.workflow}>
          <div className={s.container} style={{ textAlign: 'center' }}>
            <h2 className={s.sectionTitle} style={{ marginBottom: 64 }}>Alur Pengelolaan Bisnis</h2>
            <div className={s.workflowSteps}>
              <div className={s.workflowLine} />
              <div className={s.workflowLineMobile} />
              {product.workflow.map((step, idx) => (
                <motion.div key={idx} className={s.workflowStep} initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ delay: idx * 0.08 }}>
                  <div className={s.workflowNum} style={{ backgroundColor: product.color }}>{step.step}</div>
                  <div className={s.workflowLabel}>{step.title}</div>
                </motion.div>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* ANALYTICS + ENTERPRISE */}
      <section className={s.sectionAlt}>
        <div className={s.container}>
          <div className={s.analyticsGrid}>
            <motion.div initial="hidden" whileInView="visible" viewport={{ once: true }} variants={fadeIn}>
              <h3 className={s.subsectionTitle}>Real-time Analytics</h3>
              <div className={s.analyticsCard}>
                <div className={s.analyticsHeader}>
                  <span className={s.analyticsHeaderTitle}>Executive Summary</span>
                  <span className={s.analyticsBadge}>Live Data</span>
                </div>
                <div className={s.statsGrid}>
                  <div className={s.statBox}>
                    <div className={s.statLabel}>Total Asset / User</div>
                    <div className={s.statValue}>{product.reportingStats?.totalAsset || '12.450'}</div>
                  </div>
                  <div className={s.statBox}>
                    <div className={s.statLabel}>Acquisition Value</div>
                    <div className={s.statValue}>{product.reportingStats?.acquisitionValue || 'Rp 45.5 M'}</div>
                  </div>
                  <div className={s.statBox}>
                    <div className={s.statLabel}>Book Value / Balance</div>
                    <div className={s.statValue}>{product.reportingStats?.bookValue || 'Rp 38.2 M'}</div>
                  </div>
                  <div className={s.statBox}>
                    <div className={s.statLabel}>Monthly Status</div>
                    <div className={s.statValue}>{product.reportingStats?.depreciationMonth || 'Active'}</div>
                  </div>
                </div>
                <div className={s.chartMock}>
                  {[40, 70, 45, 90, 65, 85].map((h, i) => (
                    <div key={i} className={s.chartBar} style={{ height: `${h}%`, backgroundColor: product.color }} />
                  ))}
                </div>
              </div>
            </motion.div>
            <motion.div initial="hidden" whileInView="visible" viewport={{ once: true }} variants={fadeIn} transition={{ delay: 0.15 }}>
              <h3 className={s.subsectionTitle}>Built for Enterprise Operations</h3>
              <div className={s.capsGrid}>
                {product.enterpriseCapabilities?.map((cap, idx) => {
                  const CIcon = cap.icon;
                  return (
                    <div key={idx} className={s.capCard}>
                      <div className={s.capIcon}><CIcon size={18} /></div>
                      <span className={s.capTitle}>{cap.title}</span>
                    </div>
                  );
                })}
              </div>
            </motion.div>
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className={s.cta}>
        <div className={s.ctaContainer}>
          <motion.div className={s.ctaCard} style={{ background: `linear-gradient(135deg, ${product.color} 0%, #0F172A 100%)` }} initial={{ opacity: 0, scale: 0.96 }} whileInView={{ opacity: 1, scale: 1 }} viewport={{ once: true }} transition={{ duration: 0.5 }}>
            <h2 className={s.ctaTitle}>Bangun Ekosistem Digital<br/>yang Lebih Terintegrasi</h2>
            <p className={s.ctaDesc}>Hubungi tim konsultan kami untuk diskusi mendalam mengenai implementasi {product.name} di perusahaan Anda.</p>
            <div className={s.ctaButtons}>
              <a href="/#consultation" className={s.ctaBtnWhite}>Jadwalkan Konsultasi</a>
              <a href={product.demoUrl} target="_blank" rel="noopener noreferrer" className={s.ctaBtnGhost}>
                Coba Demo <ExternalLink size={17} />
              </a>
            </div>
          </motion.div>
        </div>
      </section>

    </div>
  );
}
