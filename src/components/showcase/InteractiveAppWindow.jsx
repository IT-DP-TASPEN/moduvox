import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import AnimatedCounter from '../ui/AnimatedCounter';

export default function InteractiveAppWindow({ appName, modules, color = '#005BAC', compact = false }) {
  const [activeModule, setActiveModule] = useState(0);
  const currentModule = modules[activeModule];

  const statusColor = (val) => {
    const lower = (val || '').toLowerCase();
    if (['aktif', 'active', 'hadir', 'lancar', 'selesai', 'lunas', 'approved', 'tersalurkan', 'siap', 'tepat waktu'].some(s => lower.includes(s))) return '#00A86B';
    if (['menunggu', 'pending', 'review', 'proses', 'draft', 'uploading', 'in progress', 'survey', 'verifikasi', 'scheduled', 'to do'].some(s => lower.includes(s))) return '#F59E0B';
    if (['terlambat', 'ditolak', 'alpha', 'failed', 'overdue', 'kurang lancar', 'belum'].some(s => lower.includes(s))) return '#EF4444';
    return '#64748B';
  };

  return (
    <div style={{
      background: '#fff',
      borderRadius: compact ? '12px' : '16px',
      border: '1px solid #E2E8F0',
      boxShadow: '0 20px 60px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.05)',
      overflow: 'hidden',
      width: '100%',
      maxWidth: compact ? '100%' : '680px',
    }}>
      {/* Title Bar */}
      <div style={{
        display: 'flex', alignItems: 'center', justifyContent: 'space-between',
        padding: '0.75rem 1rem',
        background: '#F8FAFC', borderBottom: '1px solid #E2E8F0',
      }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem' }}>
          <div style={{ display: 'flex', gap: '6px' }}>
            <span style={{ width: 12, height: 12, borderRadius: '50%', background: '#EF4444' }} />
            <span style={{ width: 12, height: 12, borderRadius: '50%', background: '#F59E0B' }} />
            <span style={{ width: 12, height: 12, borderRadius: '50%', background: '#22C55E' }} />
          </div>
          <span style={{ fontSize: '0.75rem', color: '#64748B', marginLeft: '0.5rem', fontWeight: 600 }}>{appName}</span>
        </div>
        <div style={{ display: 'flex', gap: '4px' }}>
          <span style={{ width: 14, height: 2, background: '#CBD5E1', borderRadius: 1 }} />
          <span style={{ width: 14, height: 2, background: '#CBD5E1', borderRadius: 1 }} />
          <span style={{ width: 14, height: 2, background: '#CBD5E1', borderRadius: 1 }} />
        </div>
      </div>

      <div style={{ display: 'flex', minHeight: compact ? '320px' : '400px' }}>
        {/* Sidebar */}
        <div style={{
          width: compact ? '140px' : '170px', flexShrink: 0,
          background: '#F8FAFC', borderRight: '1px solid #E2E8F0',
          padding: '0.5rem 0',
        }}>
          {modules.map((mod, idx) => {
            const Icon = mod.icon;
            const isActive = idx === activeModule;
            return (
              <button key={mod.id} onClick={() => setActiveModule(idx)} style={{
                display: 'flex', alignItems: 'center', gap: '0.5rem',
                width: '100%', padding: compact ? '0.5rem 0.75rem' : '0.6rem 1rem',
                background: isActive ? `${color}10` : 'transparent',
                borderLeft: isActive ? `3px solid ${color}` : '3px solid transparent',
                color: isActive ? color : '#64748B',
                fontSize: compact ? '0.7rem' : '0.8rem',
                fontWeight: isActive ? 600 : 400,
                transition: 'all 150ms ease',
                textAlign: 'left',
              }}>
                {Icon && <Icon size={compact ? 14 : 16} />}
                {mod.label}
              </button>
            );
          })}
        </div>

        {/* Content Panel */}
        <div style={{ flex: 1, padding: compact ? '1rem' : '1.25rem', overflow: 'hidden' }}>
          <AnimatePresence mode="wait">
            <motion.div
              key={currentModule.id}
              initial={{ opacity: 0, x: 12 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: -12 }}
              transition={{ duration: 0.2 }}
            >
              {/* KPI Widgets */}
              {currentModule.widgets && (
                <div style={{
                  display: 'grid',
                  gridTemplateColumns: `repeat(${Math.min(currentModule.widgets.length, compact ? 2 : 4)}, 1fr)`,
                  gap: compact ? '0.5rem' : '0.75rem',
                  marginBottom: '1rem',
                }}>
                  {currentModule.widgets.map((w, i) => (
                    <div key={i} style={{
                      background: '#F8FAFC', borderRadius: '8px', padding: compact ? '0.6rem' : '0.75rem',
                      border: '1px solid #E2E8F0',
                    }}>
                      <div style={{ fontSize: compact ? '0.6rem' : '0.65rem', color: '#64748B', fontWeight: 500, marginBottom: '0.25rem', textTransform: 'uppercase', letterSpacing: '0.05em' }}>{w.label}</div>
                      <div style={{ fontSize: compact ? '1rem' : '1.25rem', fontWeight: 700, color: '#1E293B' }}>
                        <AnimatedCounter value={w.value} prefix={w.prefix || ''} suffix={w.suffix || ''} displayValue={w.displayValue} duration={1} />
                      </div>
                      {w.trend && (
                        <div style={{ fontSize: '0.6rem', color: w.trend.startsWith('+') ? '#00A86B' : '#EF4444', marginTop: '0.15rem' }}>
                          {w.trend.startsWith('+') ? '↑' : '↓'} {w.trend}
                        </div>
                      )}
                    </div>
                  ))}
                </div>
              )}

              {/* Data Table */}
              {currentModule.table && (
                <div>
                  <div style={{ fontSize: compact ? '0.7rem' : '0.8rem', fontWeight: 600, color: '#1E293B', marginBottom: '0.5rem' }}>
                    {currentModule.table.title}
                  </div>
                  <div style={{ borderRadius: '8px', border: '1px solid #E2E8F0', overflow: 'hidden' }}>
                    <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: compact ? '0.6rem' : '0.7rem' }}>
                      <thead>
                        <tr style={{ background: '#F8FAFC' }}>
                          {currentModule.table.headers.map((h, i) => (
                            <th key={i} style={{ padding: compact ? '0.4rem 0.5rem' : '0.5rem 0.75rem', textAlign: 'left', color: '#64748B', fontWeight: 600, borderBottom: '1px solid #E2E8F0', whiteSpace: 'nowrap' }}>{h}</th>
                          ))}
                        </tr>
                      </thead>
                      <tbody>
                        {currentModule.table.rows.map((row, i) => (
                          <tr key={i} style={{ borderBottom: i < currentModule.table.rows.length - 1 ? '1px solid #F1F5F9' : 'none' }}>
                            {row.map((cell, j) => (
                              <td key={j} style={{ padding: compact ? '0.4rem 0.5rem' : '0.5rem 0.75rem', color: '#334155', whiteSpace: 'nowrap' }}>
                                {j === row.length - 1 || currentModule.table.headers[j]?.toLowerCase().includes('status') || currentModule.table.headers[j]?.toLowerCase().includes('kol') ? (
                                  <span style={{
                                    display: 'inline-block', padding: '2px 6px', borderRadius: '4px',
                                    fontSize: compact ? '0.55rem' : '0.65rem', fontWeight: 500,
                                    color: statusColor(cell),
                                    background: `${statusColor(cell)}10`,
                                  }}>{cell}</span>
                                ) : cell}
                              </td>
                            ))}
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                </div>
              )}
            </motion.div>
          </AnimatePresence>
        </div>
      </div>
    </div>
  );
}
