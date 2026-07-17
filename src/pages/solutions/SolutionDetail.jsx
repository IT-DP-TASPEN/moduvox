import React from 'react';
import { useParams } from 'react-router-dom';

export default function SolutionDetail() {
  const { id } = useParams();
  
  return (
    <div className="section container">
      <h2>Detail Solusi: {id}</h2>
    </div>
  );
}
