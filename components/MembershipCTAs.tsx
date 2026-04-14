import React from 'react';
import { ArrowRight } from 'lucide-react';

const MembershipCTAs: React.FC = () => {
  return (
    <section className="w-full flex flex-col">
      
      {/* Red Block - Members (Full Width) */}
      <div className="w-full bg-odtu-red relative overflow-hidden group cursor-pointer py-20 md:py-32">
        <div className="absolute inset-0 bg-gradient-to-r from-black/20 to-transparent pointer-events-none" />
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
          <div className="flex flex-col md:flex-row items-center justify-between gap-16">
            <div className="w-full md:w-2/3 text-white text-center md:text-left">
              <h2 className="text-4xl md:text-7xl font-black mb-8 leading-tight uppercase tracking-tighter">
                Üyelerimizle Varız!
              </h2>
              <p className="text-red-50 mb-12 text-xl md:text-2xl leading-relaxed font-bold opacity-90">
                Özlediğin ODTÜ ruhunu İstanbul'da yeniden keşfet. Güçlü bir dayanışma ağının parçası ol, öğrencilerin geleceğine dokun. Aramıza katıl; beraber üretelim, paylaşalım ve ODTÜ kültürünü birlikte yaşatalım!
              </p>
              <div className="flex justify-center md:justify-start">
                <button 
                  onClick={() => window.open('https://fonzip.com/odtumist/uyelik', '_blank')}
                  className="bg-white text-odtu-red px-12 py-6 rounded-full font-black text-2xl hover:bg-slate-900 hover:text-white transition-all shadow-2xl flex items-center gap-4 active:scale-95"
                >
                  ÜYE OL
                  <ArrowRight size={28} />
                </button>
              </div>
            </div>
            <div className="hidden md:flex w-1/3 justify-center items-center">
              <div className="w-64 h-64 bg-white/10 rounded-[3rem] flex items-center justify-center backdrop-blur-md border border-white/20 group-hover:scale-110 transition-transform duration-700 rotate-3 group-hover:rotate-0">
                 <span className="text-[10rem] select-none filter drop-shadow-2xl">👤</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Blue Block - Volunteers (Full Width) */}
      <div className="w-full bg-odtu-blue relative overflow-hidden group cursor-pointer py-20 md:py-32">
        <div className="absolute inset-0 bg-gradient-to-l from-black/20 to-transparent pointer-events-none" />
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
          <div className="flex flex-col md:flex-row-reverse items-center justify-between gap-16">
            <div className="w-full md:w-2/3 text-white text-center md:text-left">
              <h2 className="text-4xl md:text-7xl font-black mb-8 leading-tight uppercase tracking-tighter">
                Gönüllülerimizle Varız!
              </h2>
              <p className="text-blue-50 mb-12 text-xl md:text-2xl leading-relaxed font-bold opacity-90">
                Sosyal ve kültürel etkinliklerimizi düzenle, burs, mentorluk ve maraton gibi çalışmalara destek ver ve kendi projelerini hayata geçir!
              </p>
              <div className="flex justify-center md:justify-start">
                <button className="bg-black text-white px-12 py-6 rounded-full font-black text-2xl hover:bg-odtu-red transition-all shadow-2xl flex items-center gap-4 active:scale-95">
                  GÖNÜLLÜ OL
                  <ArrowRight size={28} />
                </button>
              </div>
            </div>
            <div className="hidden md:flex w-1/3 justify-center items-center">
              <div className="w-64 h-64 bg-white/10 rounded-[3rem] flex items-center justify-center backdrop-blur-md border border-white/20 group-hover:scale-110 transition-transform duration-700 -rotate-3 group-hover:rotate-0">
                 <span className="text-[10rem] select-none filter drop-shadow-2xl">👥</span>
              </div>
            </div>
          </div>
        </div>
      </div>

    </section>
  );
};

export default MembershipCTAs;