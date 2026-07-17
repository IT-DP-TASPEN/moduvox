import React from 'react';
import { motion } from 'framer-motion';

export default function RoleSwitcher({ roles, activeRole, onRoleChange, color = '#005BAC' }) {
  return (
    <div style={{
      display: 'inline-flex', alignItems: 'center',
      background: '#F1F5F9', borderRadius: '10px', padding: '4px',
      border: '1px solid #E2E8F0',
    }}>
      {roles.map((role) => {
        const isActive = role.id === activeRole;
        const Icon = role.icon;
        return (
          <button
            key={role.id}
            onClick={() => onRoleChange(role.id)}
            style={{
              position: 'relative',
              display: 'flex', alignItems: 'center', gap: '0.4rem',
              padding: '0.5rem 1rem',
              borderRadius: '8px',
              fontSize: '0.8rem',
              fontWeight: isActive ? 600 : 400,
              color: isActive ? '#fff' : '#64748B',
              background: isActive ? color : 'transparent',
              transition: 'all 200ms ease',
              zIndex: 1,
            }}
          >
            {Icon && <Icon size={14} />}
            {role.label}
          </button>
        );
      })}
    </div>
  );
}
