import React, { useState } from 'react';
import { Search, ChevronDown, MoreHorizontal } from 'lucide-react';

export default function DataTable({ columns, data }) {
  const [searchTerm, setSearchTerm] = useState('');

  const filteredData = data.filter(row => 
    Object.values(row).some(val => 
      String(val).toLowerCase().includes(searchTerm.toLowerCase())
    )
  );

  return (
    <div className="glass-panel" style={{ overflow: 'hidden' }}>
      <div style={{ padding: '1.5rem', display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderBottom: '1px solid var(--border-light)' }}>
        <div style={{ position: 'relative', width: '300px' }}>
          <Search size={16} color="var(--text-muted)" style={{ position: 'absolute', left: '1rem', top: '50%', transform: 'translateY(-50%)' }} />
          <input 
            type="text" 
            placeholder="Search..." 
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            style={{ 
              width: '100%', padding: '0.6rem 1rem 0.6rem 2.5rem', 
              background: 'rgba(15, 23, 42, 0.5)', border: '1px solid var(--border-light)', 
              borderRadius: '6px', color: '#fff', fontSize: '0.875rem' 
            }}
          />
        </div>
        <div>
          <button className="btn-outline" style={{ background: 'rgba(255,255,255,0.05)' }}>
            Filter <ChevronDown size={14} />
          </button>
        </div>
      </div>
      
      <div style={{ overflowX: 'auto' }}>
        <table style={{ width: '100%', borderCollapse: 'collapse', textAlign: 'left' }}>
          <thead>
            <tr style={{ background: 'rgba(255,255,255,0.02)', borderBottom: '1px solid var(--border-light)' }}>
              {columns.map((col, i) => (
                <th key={i} style={{ padding: '1rem 1.5rem', color: 'var(--text-muted)', fontSize: '0.75rem', fontWeight: '600', textTransform: 'uppercase', letterSpacing: '0.05em' }}>
                  {col.header}
                </th>
              ))}
              <th style={{ padding: '1rem 1.5rem' }}></th>
            </tr>
          </thead>
          <tbody>
            {filteredData.map((row, i) => (
              <tr key={i} style={{ borderBottom: '1px solid var(--border-light)', transition: 'background 0.2s ease' }} 
                  onMouseEnter={(e) => e.currentTarget.style.backgroundColor = 'rgba(255,255,255,0.02)'}
                  onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'transparent'}>
                {columns.map((col, j) => (
                  <td key={j} style={{ padding: '1rem 1.5rem', fontSize: '0.875rem', color: '#fff' }}>
                    {col.render ? col.render(row[col.accessor], row) : row[col.accessor]}
                  </td>
                ))}
                <td style={{ padding: '1rem 1.5rem', textAlign: 'right' }}>
                  <button style={{ color: 'var(--text-muted)' }}><MoreHorizontal size={18} /></button>
                </td>
              </tr>
            ))}
            {filteredData.length === 0 && (
              <tr>
                <td colSpan={columns.length + 1} style={{ padding: '3rem', textAlign: 'center', color: 'var(--text-muted)' }}>
                  No data found
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
      <div style={{ padding: '1rem 1.5rem', borderTop: '1px solid var(--border-light)', display: 'flex', justifyContent: 'space-between', alignItems: 'center', fontSize: '0.875rem', color: 'var(--text-muted)' }}>
        <span>Showing {filteredData.length} of {data.length} results</span>
        <div style={{ display: 'flex', gap: '0.5rem' }}>
          <button className="btn-outline" style={{ padding: '0.4rem 0.8rem' }} disabled>Previous</button>
          <button className="btn-outline" style={{ padding: '0.4rem 0.8rem' }} disabled>Next</button>
        </div>
      </div>
    </div>
  );
}
