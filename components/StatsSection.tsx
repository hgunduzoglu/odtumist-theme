import React from 'react';

const StatsSection: React.FC = () => {
  const stats = [
    { label: 'Aktif Üye', value: '5,000+' },
    { label: 'Verilen Burs', value: '12,000+' },
    { label: 'Yıllık Etkinlik', value: '150+' },
    { label: 'Kuruluş', value: '1991' },
  ];

  return (
    <div className="bg-slate-900 text-white py-16">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
          {stats.map((stat, idx) => (
            <div key={idx} className="text-center group cursor-default">
              <div className="text-4xl md:text-5xl font-bold text-white mb-2 group-hover:text-odtu-red transition-colors duration-300">
                {stat.value}
              </div>
              <div className="text-sm uppercase tracking-widest text-gray-400 font-medium">
                {stat.label}
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default StatsSection;