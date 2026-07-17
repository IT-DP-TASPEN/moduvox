import React from 'react';

export default function DashboardWidget({ title, value, subtitle, icon: Icon, trend }) {
  return (
    <div className="glass-panel" style={{ padding: '1.5rem', display: 'flex', flexDirection: 'column', gap: '1rem' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <h4 style={{ fontSize: '0.875rem', color: 'var(--text-muted)', fontWeight: '500' }}>{title}</h4>
        {Icon && <Icon size={20} color="var(--primary)" />}
      </div>
      <div>
        <div style={{ fontSize: '2rem', fontWeight: '700', color: '#fff' }}>{value}</div>
        {(subtitle || trend) && (
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', marginTop: '0.5rem', fontSize: '0.75rem' }}>
            {trend && (
              <span style={{ 
                color: trend === 'up' ? 'var(--success)' : 'var(--danger)',
                display: 'flex', alignItems: 'center', gap: '0.25rem',
                backgroundColor: trend === 'up' ? 'rgba(34, 197, 94, 0.1)' : 'rgba(239, 68, 68, 0.1)',
                padding: '2px 6px', borderRadius: '4px'
              }}>
                {trend === 'up' ? '↑' : '↓'}
              </span>
            )}
            <span style={{ color: 'var(--text-muted)' }}>{subtitle}</span>
          </div>
        )}
      </div>
    </div>
  );
}
