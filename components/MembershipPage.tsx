import React, { useState } from 'react';
import { ViewState } from '../types';
import { 
  CheckCircle2, 
  ArrowRight, 
  Wallet, 
  UserCheck, 
  HelpCircle, 
  ChevronRight, 
  ChevronLeft, 
  Sparkles, 
  Send,
  Calendar,
  Users,
  GraduationCap,
  Trophy,
  Coffee,
  Globe,
  Tag
} from 'lucide-react';

interface MembershipPageProps {
  view: ViewState;
  onNavigate: (view: ViewState) => void;
}

const WHY_SLIDES = [
  {
    step: 1,
    title: "DAYANIŞMA",
    desc: "Mezunlar, dernek çatısı altında bir araya gelir. ODTÜ'lü arkadaşları, hocaları ve öğrencilerle iletişimde kalır.",
    icon: "🤝",
    bg: "bg-orange-500"
  },
  {
    step: 2,
    title: "DERNEĞİN VARLIĞINI SÜRDÜRMESİ",
    desc: "Dernek sayesinde bağlar kuran mezunlar üye olarak derneği yaşatır; gönüllü, bağışçı ve mentor olarak çalışmaları destekler.",
    icon: "🏛️",
    bg: "bg-blue-600"
  },
  {
    step: 3,
    title: "MEZUNLARIN ÖĞRENCİLERE VE ÜNİVERSİTEYE FAYDA SAĞLAMASI",
    desc: "Bir çatı altında buluşan mezunlar, burs ve mentorluk programlarıyla öğrencilere ve dayanışmalarıyla üniversitemizin gelişimine katkı sağlar.",
    icon: "🎓",
    bg: "bg-red-600"
  },
  {
    step: 4,
    title: "YENİ MEZUNLARIN ARAMIZA KATILMASI",
    desc: "Mezun olup ODTÜ'ü hayatında tutmak isteyen öğrenciler, derneğe katılır ve zincirin bir sonraki halkası olurlar.",
    icon: "✨",
    bg: "bg-orange-400"
  },
  {
    step: 5,
    title: "CAMİAMIZ GENİŞLEDİKÇE DAYANIŞMAMIZ BÜYÜR",
    desc: "Sayıca çoğaldıkça etkimiz artar, daha fazla öğrenciye dokunur, daha büyük projelere imza atarız.",
    icon: "🌍",
    bg: "bg-indigo-600"
  }
];

const BENEFIT_CARDS = [
  {
    id: 1,
    title: "ETKİNLİKLER",
    items: ["Atölyeler", "Seminerler", "Geziler", "Spor Etkinlikleri"],
    icon: <Calendar className="text-odtu-blue" size={32} />,
    color: "border-odtu-blue",
    img: "https://images.unsplash.com/photo-1528605248644-14dd04022da1?auto=format&fit=crop&q=80&w=800"
  },
  {
    id: 2,
    title: "ÇALIŞMA GRUPLARI",
    items: ["Edebiyat", "Fotoğraf", "Enerji", "Felsefe..."],
    icon: <Users className="text-orange-500" size={32} />,
    color: "border-orange-500",
    img: "https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&q=80&w=800"
  },
  {
    id: 3,
    title: "BURS ÇALIŞMALARI",
    items: ["Bursiyerlerle Etkinlikler", "Eğitim Desteği", "Geleceğe Yatırım"],
    icon: <GraduationCap className="text-odtu-red" size={32} />,
    color: "border-odtu-red",
    img: "https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&q=80&w=800"
  },
  {
    id: 4,
    title: "SPOR & MARATON",
    items: ["Maraton Çalışmaları", "Koşu Grupları", "İyilik Peşinde Koş"],
    icon: <Trophy className="text-odtu-blue" size={32} />,
    color: "border-odtu-blue",
    img: "https://odtumist.org/wp-content/uploads/2021/01/ODTMST-Spr-Maraton-KV1-Banner-02.jpg"
  },
  {
    id: 5,
    title: "MENTORLUK",
    items: ["Mentorluk Görüşmeleri", "Kariyer Rehberliği", "Deneyim Paylaşımı"],
    icon: <Coffee className="text-odtu-red" size={32} />,
    color: "border-odtu-red",
    img: "https://images.unsplash.com/photo-1517245385161-12499d63428c?auto=format&fit=crop&q=80&w=800"
  },
  {
    id: 6,
    title: "ODTÜ RUHU",
    items: ["Networking", "Birlikte Üretme", "İletişim İmkanları"],
    icon: <Globe className="text-indigo-600" size={32} />,
    color: "border-indigo-600",
    img: "https://images.unsplash.com/photo-1523580494863-6f3031224c94?auto=format&fit=crop&q=80&w=800"
  }
];

const BRANDS = [
  "YÖN Kariyer", "TOWER PUB", "terra pores", "TOLEMUS", "samm teknoloji", "RİTİM GALATA", "yataş bedding",
  "RResfeber", "AKBAL", "VINA", "MÜJGAN ÇETİN", "Mplus", "Miyaevent", "akmeric",
  "mirasis", "MINDFULNESS", "Kaktüs", "FRÖBEL", "IFREDİ", "EURO TECH", "ZEPLİN CAR"
];

const MembershipPage: React.FC<MembershipPageProps> = ({ view, onNavigate }) => {
  const [activeSlide, setActiveSlide] = useState(0);

  const renderWhySection = () => (
    <div className="animate-fade-in space-y-20">
      <div className="text-center max-w-4xl mx-auto">
        <h2 className="text-5xl md:text-7xl font-black text-slate-900 mb-8 uppercase tracking-tighter">
          NEDEN <span className="text-odtu-blue">ÜYE OLMALIYIM?</span>
        </h2>
        <div className="w-24 h-2 bg-odtu-red mx-auto mb-10 rounded-full" />
      </div>

      <div className="relative bg-white rounded-[3rem] shadow-2xl border border-gray-100 overflow-hidden flex flex-col md:flex-row min-h-[500px]">
        <div className={`w-full md:w-1/2 p-12 md:p-20 text-white flex flex-col justify-center transition-colors duration-500 ${WHY_SLIDES[activeSlide].bg}`}>
          <span className="text-8xl font-black opacity-20 mb-4">{WHY_SLIDES[activeSlide].step}</span>
          <h3 className="text-3xl md:text-4xl font-black mb-6 leading-tight uppercase">
            {WHY_SLIDES[activeSlide].title}
          </h3>
          <p className="text-xl leading-relaxed opacity-90">
            {WHY_SLIDES[activeSlide].desc}
          </p>
          
          <div className="flex gap-4 mt-12">
            <button 
              onClick={() => setActiveSlide(prev => (prev === 0 ? WHY_SLIDES.length - 1 : prev - 1))}
              className="p-3 rounded-full bg-white/20 hover:bg-white/40 transition-all"
            >
              <ChevronLeft size={24} />
            </button>
            <button 
              onClick={() => setActiveSlide(prev => (prev + 1) % WHY_SLIDES.length)}
              className="p-3 rounded-full bg-white/20 hover:bg-white/40 transition-all"
            >
              <ChevronRight size={24} />
            </button>
          </div>
        </div>
        
        <div className="w-full md:w-1/2 bg-slate-50 flex items-center justify-center p-12">
           <span className="text-[12rem] select-none animate-bounce">{WHY_SLIDES[activeSlide].icon}</span>
        </div>
      </div>

      {/* Transition Highlight */}
      <div className="text-center py-10">
        <h3 className="text-2xl md:text-5xl font-black text-slate-900 uppercase tracking-tighter leading-tight">
          Mezunlar Derneği'ne üye olan <span className="text-odtu-red">ODTÜ'lüler;</span>
        </h3>
        <div className="flex justify-center gap-2 mt-6">
           <div className="w-12 h-1.5 bg-odtu-blue rounded-full" />
           <div className="w-24 h-1.5 bg-odtu-red rounded-full" />
           <div className="w-12 h-1.5 bg-odtu-blue rounded-full" />
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {[
          { title: "Kendini ifade edecek bir alan bulur.", icon: "💬" },
          { title: "Sivil topluma ve aktif demokrasiye katılır.", icon: "🗳️" },
          { title: "Üniversitemize olan gönül borcunu öder.", icon: "💝" },
          { title: "ODTÜ mirasını yeni nesillere aktarır.", icon: "🕯️" }
        ].map((item, idx) => (
          <div key={idx} className="p-8 bg-slate-50 rounded-3xl border border-slate-100 hover:border-odtu-red transition-all group">
            <span className="text-4xl block mb-6">{item.icon}</span>
            <h4 className="font-bold text-slate-800 leading-snug group-hover:text-odtu-red transition-colors">
              {item.title}
            </h4>
          </div>
        ))}
      </div>

      <div className="bg-odtu-red rounded-[2.5rem] p-12 text-center text-white">
        <h3 className="text-3xl md:text-5xl font-black mb-8 leading-tight">
          HADİ ŞİMDİ DE BU 'ÇATIDA' BULUŞALIM
        </h3>
        <button 
           onClick={() => window.open('https://fonzip.com/odtumist/uyelik', '_blank')}
           className="bg-white text-odtu-red px-12 py-5 rounded-full font-black text-xl hover:bg-slate-900 hover:text-white transition-all shadow-xl"
        >
          ŞİMDİ ÜYE OL
        </button>
      </div>
    </div>
  );

  const renderBenefitsSection = () => (
    <div className="animate-fade-in space-y-24">
      {/* Header */}
      <div className="text-center max-w-4xl mx-auto">
        <h2 className="text-5xl md:text-7xl font-black text-slate-900 mb-8 uppercase tracking-tighter">
          ÜYELİK <span className="text-orange-500">AVANTAJLARI</span>
        </h2>
        <p className="text-xl text-slate-500 font-medium">ODTÜMİST üyesi olarak yararlanabileceğiniz ayrıcalıklar dünyasını keşfedin.</p>
        <div className="w-24 h-2 bg-odtu-blue mx-auto mt-10 rounded-full" />
      </div>

      {/* Benefits Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        {BENEFIT_CARDS.map((card) => (
          <div key={card.id} className={`group bg-white rounded-[2.5rem] overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 border-2 ${card.color} flex flex-col`}>
            <div className="relative h-56 overflow-hidden">
               <img src={card.img} alt={card.title} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000" />
               <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />
               <div className="absolute top-6 left-6 w-14 h-14 bg-white rounded-full flex items-center justify-center shadow-lg font-black text-2xl text-slate-900">
                  {card.id}
               </div>
               <div className="absolute bottom-6 left-6 text-white">
                 <h3 className="text-2xl font-black tracking-tight">{card.title}</h3>
               </div>
            </div>
            <div className="p-8 flex-1 flex flex-col">
               <div className="mb-6">{card.icon}</div>
               <ul className="space-y-3 mb-8">
                 {card.items.map((item, i) => (
                   <li key={i} className="flex items-center gap-2 text-slate-600 font-bold">
                     <div className="w-1.5 h-1.5 rounded-full bg-odtu-red" />
                     {item}
                   </li>
                 ))}
               </ul>
               <button className="mt-auto flex items-center gap-2 text-sm font-black uppercase tracking-widest text-odtu-blue hover:text-odtu-red transition-colors group/btn">
                 DETAYLARI GÖR
                 <ChevronRight size={18} className="group-hover/btn:translate-x-1 transition-transform" />
               </button>
            </div>
          </div>
        ))}
      </div>

      {/* Brands Wall */}
      <div className="bg-slate-900 rounded-[3.5rem] p-12 md:p-20 text-center relative overflow-hidden">
        <div className="absolute top-0 right-0 w-64 h-64 bg-odtu-blue/20 blur-[100px] rounded-full" />
        <div className="relative z-10">
          <div className="inline-flex items-center gap-3 px-6 py-2 bg-odtu-blue rounded-full text-white text-xs font-black uppercase tracking-[0.2em] mb-8">
            <Tag size={14} />
            İndirim Yapan Firmalar
          </div>
          <h3 className="text-3xl md:text-5xl font-black text-white mb-12 uppercase">
            ÜYELERE ÖZEL ORANLARDA <br />
            <span className="text-odtu-blue">İNDİRİM YAPAN MARKALAR</span>
          </h3>
          
          <div className="flex flex-wrap justify-center gap-4">
            {BRANDS.map((brand, idx) => (
              <div key={idx} className="px-6 py-4 bg-white/5 border border-white/10 rounded-2xl text-white font-bold hover:bg-white hover:text-slate-900 transition-all cursor-default">
                {brand}
              </div>
            ))}
          </div>
          
          <p className="mt-12 text-slate-400 font-medium">Ve daha onlarca anlaşmalı kurum...</p>
        </div>
      </div>

      {/* Final ODTÜ Spirit CTA */}
      <div className="bg-orange-500 rounded-[3rem] p-12 md:p-20 flex flex-col md:flex-row items-center justify-between gap-12 text-white">
        <div className="w-full md:w-2/3">
          <h3 className="text-3xl md:text-6xl font-black mb-8 leading-tight uppercase">
            HADİ ŞİMDİ DE BU <br />
            <span className="text-slate-900">'ÇATIDA'</span> BULUŞALIM
          </h3>
          <p className="text-xl md:text-2xl font-bold opacity-90 mb-0">
            En önemlisi ODTÜ'lülerle birlikte olma, birlikte üretme: <br />
            <span className="bg-slate-900/20 px-4 py-1 rounded-lg mt-4 inline-block">İLETİŞİM VE NETWORKING İMKANLARI!</span>
          </p>
        </div>
        <div className="w-full md:w-1/3 flex justify-center md:justify-end">
          <button 
             onClick={() => window.open('https://fonzip.com/odtumist/uyelik', '_blank')}
             className="bg-white text-orange-600 px-12 py-6 rounded-full font-black text-2xl hover:bg-slate-900 hover:text-white transition-all shadow-2xl active:scale-95"
          >
            ÜYE OL
          </button>
        </div>
      </div>
    </div>
  );

  const renderUpdateForm = () => (
    <div className="animate-fade-in max-w-4xl mx-auto">
      <div className="text-center mb-12">
        <h2 className="text-4xl font-black text-slate-900 mb-4">BİLGİ GÜNCELLEME</h2>
        <p className="text-slate-500">Ağımızı canlı tutmak için iletişim bilgilerinizi güncel tutmanız bizim için çok değerli Hocam.</p>
      </div>
      
      <form className="bg-white rounded-[2rem] shadow-2xl border border-gray-100 p-8 md:p-12 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="space-y-2">
          <label className="text-sm font-bold text-slate-700 uppercase">Ad</label>
          <input type="text" className="w-full p-4 bg-slate-50 rounded-xl border border-slate-200 outline-none focus:border-odtu-red transition-all" placeholder="Adınız" />
        </div>
        <div className="space-y-2">
          <label className="text-sm font-bold text-slate-700 uppercase">Soyad</label>
          <input type="text" className="w-full p-4 bg-slate-50 rounded-xl border border-slate-200 outline-none focus:border-odtu-red transition-all" placeholder="Soyadınız" />
        </div>
        <div className="space-y-2">
          <label className="text-sm font-bold text-slate-700 uppercase">E-Posta</label>
          <input type="email" className="w-full p-4 bg-slate-50 rounded-xl border border-slate-200 outline-none focus:border-odtu-red transition-all" placeholder="hocam@example.com" />
        </div>
        <div className="space-y-2">
          <label className="text-sm font-bold text-slate-700 uppercase">Telefon</label>
          <input type="tel" className="w-full p-4 bg-slate-50 rounded-xl border border-slate-200 outline-none focus:border-odtu-red transition-all" placeholder="05xx xxx xx xx" />
        </div>
        <div className="space-y-2">
          <label className="text-sm font-bold text-slate-700 uppercase">Mezuniyet Yılı</label>
          <input type="number" className="w-full p-4 bg-slate-50 rounded-xl border border-slate-200 outline-none focus:border-odtu-red transition-all" placeholder="Örn: 1995" />
        </div>
        <div className="space-y-2">
          <label className="text-sm font-bold text-slate-700 uppercase">Bölüm</label>
          <input type="text" className="w-full p-4 bg-slate-50 rounded-xl border border-slate-200 outline-none focus:border-odtu-red transition-all" placeholder="Mezun olduğunuz bölüm" />
        </div>
        <div className="md:col-span-2 space-y-2">
          <label className="text-sm font-bold text-slate-700 uppercase">İş Bilgileri</label>
          <input type="text" className="w-full p-4 bg-slate-50 rounded-xl border border-slate-200 outline-none focus:border-odtu-red transition-all" placeholder="Şirket, Ünvan vb." />
        </div>
        <div className="space-y-2">
          <label className="text-sm font-bold text-slate-700 uppercase">Yaşadığınız İl</label>
          <input type="text" className="w-full p-4 bg-slate-50 rounded-xl border border-slate-200 outline-none focus:border-odtu-red transition-all" placeholder="İl" />
        </div>
        <div className="space-y-2">
          <label className="text-sm font-bold text-slate-700 uppercase">İlçe</label>
          <input type="text" className="w-full p-4 bg-slate-50 rounded-xl border border-slate-200 outline-none focus:border-odtu-red transition-all" placeholder="İlçe" />
        </div>
        <div className="md:col-span-2 space-y-2">
          <label className="text-sm font-bold text-slate-700 uppercase">Derneğe İletmek İstedikleriniz</label>
          <textarea rows={4} className="w-full p-4 bg-slate-50 rounded-xl border border-slate-200 outline-none focus:border-odtu-red transition-all resize-none" placeholder="Görüş ve önerileriniz..."></textarea>
        </div>
        <div className="md:col-span-2 mt-4">
          <button type="submit" className="w-full p-5 bg-slate-900 text-white rounded-xl font-bold text-lg hover:bg-odtu-red transition-all flex items-center justify-center gap-3">
            BİLGİLERİMİ GÜNCELLE
            <Send size={20} />
          </button>
        </div>
      </form>
    </div>
  );

  const renderDuesSection = () => (
    <div className="animate-fade-in max-w-3xl mx-auto text-center py-12">
      <div className="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center text-odtu-blue mx-auto mb-8">
        <Wallet size={48} />
      </div>
      <h2 className="text-4xl font-black text-slate-900 mb-6 uppercase">AİDAT ÖDEME</h2>
      <p className="text-xl text-slate-600 mb-10 leading-relaxed">
        Aidat borcunuzu görüntülemek, tek seferde veya taksitle ödemek için Üye Sistemimiz <span className="font-bold text-odtu-blue">Fonzip'e</span> giriş yapın.
      </p>
      
      <div className="bg-slate-50 p-8 rounded-[2rem] border border-slate-100 text-left mb-12">
        <h4 className="font-bold text-slate-800 mb-4 flex items-center gap-2">
          <CheckCircle2 className="text-green-500" />
          Neden Aidat Ödemeliyim?
        </h4>
        <p className="text-sm text-slate-500 leading-relaxed">
          Ödediğiniz her aidat, derneğimizin bağımsızlığını korumasını, etkinliklerimizin sürekliliğini ve daha fazla ODTÜ öğrencisine burs imkanı sağlamamızı mümkün kılıyor. ODTÜ ruhu sizin desteğinizle yaşıyor.
        </p>
      </div>

      <button 
        onClick={() => window.open('https://fonzip.com/odtumist/login', '_blank')}
        className="px-12 py-5 bg-odtu-blue text-white rounded-full font-black text-xl hover:bg-blue-800 transition-all shadow-xl flex items-center justify-center gap-3 mx-auto"
      >
        FONZİP'E GİRİŞ YAP
        <ArrowRight size={24} />
      </button>
    </div>
  );

  return (
    <div className="pt-32 pb-20 bg-slate-50 min-h-screen">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {view === ViewState.MEMBERSHIP_WHY && renderWhySection()}
        {view === ViewState.MEMBERSHIP_UPDATE && renderUpdateForm()}
        {view === ViewState.MEMBERSHIP_DUES && renderDuesSection()}
        {view === ViewState.MEMBERSHIP_BENEFITS && renderBenefitsSection()}
      </div>
    </div>
  );
};

export default MembershipPage;