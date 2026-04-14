
import React from 'react';
import { Mail, Phone, MapPin, Instagram, Linkedin, Facebook, Youtube, Send, ChevronRight, MessageSquare, Users } from 'lucide-react';
import { SOCIAL_LINKS } from '../constants';

const XIcon = ({ size = 18 }: { size?: number }) => (
  <svg width={size} height={size} viewBox="0 0 24 24" fill="currentColor">
    <path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932 6.064-6.932zm-1.292 19.494h2.039L6.486 3.24H4.298l13.311 17.407z" />
  </svg>
);

const ContactPage: React.FC = () => {
  return (
    <div className="bg-white min-h-screen">
      {/* Hero Section */}
      <section className="relative h-[65vh] min-h-[500px] overflow-hidden">
        <div className="absolute inset-0 bg-black/30 z-10" />
        <div className="absolute inset-0 flex items-center justify-center z-0">
           <img 
             src="https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&q=80&w=2000" 
             alt="ODTÜMİST Tunus Otobüsü" 
             className="w-full h-full object-cover scale-100 transition-transform duration-[10s] animate-pulse"
             onError={(e) => {
                (e.target as HTMLImageElement).src = "https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&q=80&w=2000";
             }}
           />
        </div>
        <div className="absolute inset-0 z-20 flex items-center justify-center text-center px-4">
          <div className="max-w-4xl bg-black/40 backdrop-blur-md p-10 rounded-[3.5rem] border border-white/20 shadow-2xl">
            <h1 className="text-5xl md:text-8xl font-black text-white mb-6 uppercase tracking-tighter animate-slide-up drop-shadow-2xl">
              BİZE <span className="text-odtu-red">ULAŞIN</span>
            </h1>
            <p className="text-xl md:text-2xl text-white font-bold max-w-2xl mx-auto leading-relaxed animate-fade-in drop-shadow-lg">
              İstanbul'daki ODTÜ ruhunun merkezi ODTÜPARK'ta sizleri bekliyoruz Hocam.
            </p>
          </div>
        </div>
        <div className="absolute bottom-0 left-0 w-full overflow-hidden leading-none z-30">
          <svg viewBox="0 0 1200 120" preserveAspectRatio="none" className="relative block w-[calc(100%+1.3px)] h-20 fill-white">
            <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5,73.84-4.36,147.54,16.88,218.2,35.26,69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113,2.03,1200,0V0Z"></path>
          </svg>
        </div>
      </section>

      {/* Main Content Grid */}
      <section className="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12">
          
          {/* Left Column */}
          <div className="lg:col-span-7 space-y-8">
            <div className="bg-slate-900 rounded-[3.5rem] p-10 md:p-14 text-white relative overflow-hidden shadow-2xl border-b-[12px] border-odtu-blue">
               <div className="absolute top-0 right-0 w-64 h-64 bg-odtu-blue/20 blur-[100px] rounded-full" />
               <div className="relative z-10 flex flex-col md:flex-row gap-12">
                  <div className="flex-1">
                    <h3 className="text-3xl font-black mb-8 uppercase tracking-tight flex items-center gap-3">
                      <div className="w-2 h-8 bg-odtu-blue rounded-full" />
                      İletişim Bilgileri
                    </h3>
                    <div className="space-y-6">
                       <div className="flex items-start gap-4 group">
                         <div className="p-3 bg-white/10 rounded-2xl group-hover:bg-odtu-blue transition-colors">
                           <MapPin className="shrink-0" size={20} />
                         </div>
                         <div>
                           <p className="font-bold text-lg">ODTÜPARK Ulus</p>
                           <p className="text-slate-400">Levazım Mah. Koru Sok. Beşiktaş/İstanbul</p>
                         </div>
                       </div>
                       <div className="flex items-center gap-4 group">
                         <div className="p-3 bg-white/10 rounded-2xl group-hover:bg-odtu-blue transition-colors">
                           <Phone className="shrink-0" size={20} />
                         </div>
                         <p className="font-bold text-lg">+90 (212) 281 40 47</p>
                       </div>
                    </div>
                  </div>
                  <div className="md:w-px bg-white/10" />
                  <div className="flex-1">
                    <h3 className="text-xl font-bold mb-8 uppercase tracking-widest text-slate-400">Sosyal Medya</h3>
                    <div className="grid grid-cols-3 gap-4">
                      <a href={SOCIAL_LINKS.instagram} target="_blank" rel="noreferrer" className="w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center hover:bg-odtu-red hover:border-odtu-red transition-all">
                        <Instagram size={24} />
                      </a>
                      <a href={SOCIAL_LINKS.linkedin} target="_blank" rel="noreferrer" className="w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center hover:bg-odtu-blue hover:border-odtu-blue transition-all">
                        <Linkedin size={24} />
                      </a>
                      <a href={SOCIAL_LINKS.x} target="_blank" rel="noreferrer" className="w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center hover:bg-slate-700 hover:border-slate-700 transition-all">
                        <XIcon size={22} />
                      </a>
                      <a href={SOCIAL_LINKS.facebook} target="_blank" rel="noreferrer" className="w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center hover:bg-blue-600 hover:border-blue-600 transition-all">
                        <Facebook size={24} />
                      </a>
                      <a href={SOCIAL_LINKS.youtube} target="_blank" rel="noreferrer" className="w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center hover:bg-red-600 hover:border-red-600 transition-all">
                        <Youtube size={24} />
                      </a>
                    </div>
                  </div>
               </div>
            </div>

            <div className="space-y-4">
               <div className="bg-slate-50 p-8 rounded-[2.5rem] border border-slate-100 flex flex-col md:flex-row items-center justify-between gap-6 hover:shadow-xl transition-all group">
                 <div className="flex items-center gap-6 text-center md:text-left">
                   <div className="w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center text-odtu-red group-hover:bg-odtu-red group-hover:text-white transition-all">
                     <Users size={32} />
                   </div>
                   <div>
                     <h4 className="text-xl font-bold text-slate-900">Yönetim Kurulu & Üyelik</h4>
                     <p className="text-slate-500 text-sm">Üyelik süreçleri ve genel iletişim</p>
                   </div>
                 </div>
                 <a href="mailto:dernek@odtumist.org" className="text-odtu-red font-black text-lg flex items-center gap-2 hover:translate-x-2 transition-transform">
                   dernek@odtumist.org
                   <ChevronRight />
                 </a>
               </div>

               <div className="bg-slate-50 p-8 rounded-[2.5rem] border border-slate-100 flex flex-col md:flex-row items-center justify-between gap-6 hover:shadow-xl transition-all group">
                 <div className="flex items-center gap-6 text-center md:text-left">
                   <div className="w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center text-odtu-blue group-hover:bg-odtu-blue group-hover:text-white transition-all">
                     <MessageSquare size={32} />
                   </div>
                   <div>
                     <h4 className="text-xl font-bold text-slate-900">Dernek Koordinatörü</h4>
                     <p className="text-odtu-blue font-bold text-sm uppercase tracking-wide">Buket Akpınar</p>
                   </div>
                 </div>
                 <a href="mailto:buket.akpinar@odtumist.org" className="text-odtu-blue font-black text-lg flex items-center gap-2 hover:translate-x-2 transition-transform">
                   buket.akpinar@odtumist.org
                   <ChevronRight />
                 </a>
               </div>

               <div className="bg-slate-50 p-8 rounded-[2.5rem] border border-slate-100 flex flex-col md:flex-row items-center justify-between gap-6 hover:shadow-xl transition-all group">
                 <div className="flex items-center gap-6 text-center md:text-left">
                   <div className="w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center text-odtu-red group-hover:bg-odtu-red group-hover:text-white transition-all">
                     <Mail size={32} />
                   </div>
                   <div>
                     <h4 className="text-xl font-bold text-slate-900">Burs Sorumlusu</h4>
                     <p className="text-odtu-red font-bold text-sm uppercase tracking-wide">Delal Filizay</p>
                   </div>
                 </div>
                 <a href="mailto:delal.filizay@odtumist.org" className="text-odtu-red font-black text-lg flex items-center gap-2 hover:translate-x-2 transition-transform">
                   delal.filizay@odtumist.org
                   <ChevronRight />
                 </a>
               </div>
            </div>
          </div>

          {/* Right Column */}
          <div className="lg:col-span-5">
             <div className="sticky top-32 space-y-8">
                <div className="text-center md:text-left">
                   <h3 className="text-3xl font-black text-slate-900 uppercase tracking-tighter">
                     Buluşma Noktamız: <br />
                     <span className="text-odtu-blue">Ulus ODTÜPARK</span>
                   </h3>
                   <div className="w-20 h-2 bg-odtu-red rounded-full mt-4 mx-auto md:mx-0" />
                </div>
                
                <div className="w-full aspect-[4/5] rounded-[4rem] overflow-hidden shadow-[0_30px_60px_-15px_rgba(0,0,0,0.3)] border-8 border-slate-50">
                  <iframe 
                      src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3008.38870198594!2d29.02340337656644!3d41.06047247134375!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14cab660f7e4f35b%3A0x868b20463c630807!2zT0RUw5xNwLBTVCBWacWfbmVsaWsgVGVzaXNsZXJp!5e0!3m2!1str!2str!4v1700000000000!5m2!1str!2str" 
                      width="100%" 
                      height="100%" 
                      style={{ border: 0 }} 
                      allowFullScreen={true} 
                      loading="lazy" 
                      referrerPolicy="no-referrer-when-downgrade"
                      title="ODTÜMİST Lokasyon"
                    ></iframe>
                </div>
                
                <div className="flex justify-center">
                  <a 
                    href="https://maps.app.goo.gl/QGGZtNl7QrMxFSI6L" 
                    target="_blank" 
                    rel="noreferrer"
                    className="flex items-center gap-3 px-12 py-5 bg-slate-900 text-white rounded-full font-black text-sm hover:bg-odtu-red transition-all shadow-2xl hover:-translate-y-2 active:scale-95"
                  >
                    YOL ALMAYA BAŞLA
                    <Send size={20} />
                  </a>
                </div>
             </div>
          </div>
        </div>
      </section>

      {/* Message Form Section */}
      <section className="bg-slate-50 py-24 border-t border-slate-200">
        <div className="max-w-4xl mx-auto px-4">
          <div className="text-center mb-16">
            <h2 className="text-4xl md:text-6xl font-black mb-6 uppercase tracking-tighter text-slate-900">SİZİ DİNLEMEYE HAZIRIZ</h2>
            <p className="text-xl text-slate-500 max-w-2xl mx-auto font-medium">Bize her konuda yazabilirsiniz Hocam.</p>
          </div>

          <div className="bg-white rounded-[3.5rem] shadow-2xl border border-slate-100 p-8 md:p-16">
            <h3 className="text-2xl font-black text-slate-900 mb-8 uppercase text-center md:text-left">BİZE MESAJ GÖNDERİN</h3>
            <form className="space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="space-y-2">
                  <label className="text-xs font-black uppercase tracking-widest text-slate-400 ml-4">Adınız Soyadınız</label>
                  <input type="text" placeholder="Hocam..." className="w-full px-6 py-4 bg-slate-50 rounded-2xl border-2 border-transparent focus:border-odtu-blue focus:bg-white outline-none transition-all font-medium" />
                </div>
                <div className="space-y-2">
                  <label className="text-xs font-black uppercase tracking-widest text-slate-400 ml-4">E-posta Adresiniz</label>
                  <input type="email" placeholder="ornek@mail.com" className="w-full px-6 py-4 bg-slate-50 rounded-2xl border-2 border-transparent focus:border-odtu-blue focus:bg-white outline-none transition-all font-medium" />
                </div>
              </div>
              <div className="space-y-2">
                <label className="text-xs font-black uppercase tracking-widest text-slate-400 ml-4">Mesajınız</label>
                <textarea rows={6} placeholder="Sorularınız, önerileriniz veya paylaşmak istedikleriniz..." className="w-full px-6 py-4 bg-slate-50 rounded-2xl border-2 border-transparent focus:border-odtu-blue focus:bg-white outline-none transition-all font-medium resize-none"></textarea>
              </div>
              <div className="pt-4 flex justify-center md:justify-end">
                <button type="submit" className="bg-odtu-blue text-white px-16 py-6 rounded-full font-black text-xl hover:bg-slate-900 transition-all shadow-2xl active:scale-95 flex items-center gap-4">
                  GÖNDER
                  <Send size={24} />
                </button>
              </div>
            </form>
          </div>
        </div>
      </section>
    </div>
  );
};

export default ContactPage;
