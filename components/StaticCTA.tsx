import React from 'react';
import { ArrowRight } from 'lucide-react';

const StaticCTA: React.FC = () => {
  return (
    <section className="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div className="relative bg-odtu-red rounded-3xl overflow-hidden shadow-2xl shadow-red-600/20 flex flex-col md:flex-row">
        
        {/* Decorative Shape */}
        <div className="absolute top-0 right-0 w-2/3 h-full bg-white transform skew-x-12 translate-x-32 md:translate-x-48 z-0 rounded-l-[100px]" />
        
        {/* Text Content */}
        <div className="relative z-10 w-full md:w-1/2 p-10 md:p-16 flex flex-col justify-center text-white">
          <h2 className="text-3xl md:text-4xl font-bold mb-6 leading-tight">
            Toplum Gönüllüsü Ol!
          </h2>
          <p className="text-red-100 mb-8 text-lg leading-relaxed">
            Çevrelerinde gördükleri farklı ihtiyaçlara/problemlere projeleri aracılığı ile çözüm üreten, herkesi harekete geçmeye davet eden, kendine güvenen Toplum Gönüllüsü ODTÜ'lülere katıl, değişime kendinden başla!
          </p>
          <div>
            <button className="bg-black text-white px-8 py-4 rounded-full font-bold hover:bg-slate-800 transition-colors inline-flex items-center gap-3">
              Gönüllü Ol
              <ArrowRight size={18} />
            </button>
          </div>
        </div>

        {/* Image/Graphic Area */}
        <div className="relative z-10 w-full md:w-1/2 min-h-[300px] md:min-h-auto flex items-center justify-center p-8">
           {/* Using a representative illustration image */}
           <img 
             src="https://cdn3d.iconscout.com/3d/premium/thumb/teamwork-5495869-4576652.png" 
             alt="Volunteering" 
             className="w-full max-w-md object-contain drop-shadow-xl"
           />
        </div>
      </div>
    </section>
  );
};

export default StaticCTA;