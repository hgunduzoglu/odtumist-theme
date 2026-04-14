
import React from 'react';
import { Facebook, Instagram, Linkedin, Mail, Phone, MapPin, Youtube } from 'lucide-react';
import { SOCIAL_LINKS } from '../constants';

// Custom X (formerly Twitter) Icon
const XIcon = ({ size = 18 }: { size?: number }) => (
  <svg width={size} height={size} viewBox="0 0 24 24" fill="currentColor">
    <path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932 6.064-6.932zm-1.292 19.494h2.039L6.486 3.24H4.298l13.311 17.407z" />
  </svg>
);

const Footer: React.FC = () => {
  return (
    <footer className="bg-white border-t border-gray-200 pt-16 pb-8">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-12 mb-12">
          
          {/* Brand & Logo Column */}
          <div className="lg:col-span-2 space-y-6">
            <div className="flex items-center gap-3">
              <div className="w-16 h-16 bg-odtu-red rounded-full flex items-center justify-center text-white font-bold text-2xl shadow-lg">O</div>
              <div>
                <h2 className="font-bold text-2xl text-slate-900">ODTÜMİST</h2>
                <p className="text-sm text-slate-500 tracking-wider uppercase">İstanbul ODTÜ Mezunlar Derneği</p>
              </div>
            </div>
            <p className="text-gray-600 text-sm leading-relaxed max-w-md">
              Mezunlarımız arasındaki dayanışmayı artırmak, üniversitemize katkı sağlamak ve toplumsal fayda üretmek amacıyla İstanbul'da faaliyet gösteren köklü bir sivil toplum kuruluşudur.
            </p>
            <div className="flex space-x-3 pt-2">
              <a href={SOCIAL_LINKS.instagram} target="_blank" rel="noreferrer" className="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-odtu-red hover:text-white transition-all shadow-sm">
                <Instagram size={18} />
              </a>
              <a href={SOCIAL_LINKS.linkedin} target="_blank" rel="noreferrer" className="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-odtu-blue hover:text-white transition-all shadow-sm">
                <Linkedin size={18} />
              </a>
              <a href={SOCIAL_LINKS.x} target="_blank" rel="noreferrer" className="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-slate-900 hover:text-white transition-all shadow-sm">
                <XIcon size={16} />
              </a>
              <a href={SOCIAL_LINKS.facebook} target="_blank" rel="noreferrer" className="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                <Facebook size={18} />
              </a>
              <a href={SOCIAL_LINKS.youtube} target="_blank" rel="noreferrer" className="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-red-600 hover:text-white transition-all shadow-sm">
                <Youtube size={18} />
              </a>
            </div>
          </div>

          {/* Column 1 */}
          <div>
            <h4 className="font-bold text-slate-900 mb-6 border-l-4 border-odtu-red pl-3">Kurumsal</h4>
            <ul className="space-y-3 text-sm text-gray-600">
              <li><a href="#" className="hover:text-odtu-red transition-colors hover:underline">Bir Bakışta ODTÜMİST</a></li>
              <li><a href="#" className="hover:text-odtu-red transition-colors hover:underline">Paydaşlarımız</a></li>
              <li><a href="#" className="hover:text-odtu-red transition-colors hover:underline">Tarihçe</a></li>
              <li><a href="#" className="hover:text-odtu-red transition-colors hover:underline">Yönetim</a></li>
            </ul>
          </div>

          {/* Column 2 */}
          <div>
            <h4 className="font-bold text-slate-900 mb-6 border-l-4 border-odtu-blue pl-3">Bilgi Merkezi</h4>
            <ul className="space-y-3 text-sm text-gray-600">
              <li><a href="#" className="hover:text-odtu-blue transition-colors hover:underline">Raporlar</a></li>
              <li><a href="#" className="hover:text-odtu-blue transition-colors hover:underline">KVKK</a></li>
              <li><a href="#" className="hover:text-odtu-blue transition-colors hover:underline">Üyelik Şartları</a></li>
              <li><a href="#" className="hover:text-odtu-blue transition-colors hover:underline">Sıkça Sorulan Sorular</a></li>
            </ul>
          </div>

          {/* Contact */}
          <div>
            <h4 className="font-bold text-slate-900 mb-6 border-l-4 border-gray-400 pl-3">İletişim</h4>
            <ul className="space-y-4 text-sm text-gray-600">
              <li className="flex items-start gap-3 group">
                <MapPin size={18} className="text-odtu-red shrink-0 mt-0.5 group-hover:animate-bounce" />
                <span className="group-hover:text-slate-900 transition-colors">Levazım Mah. Koru Sok. Beşiktaş, İstanbul (ODTÜPARK)</span>
              </li>
              <li className="flex items-center gap-3 group">
                <Phone size={18} className="text-odtu-red shrink-0 group-hover:rotate-12 transition-transform" />
                <span className="group-hover:text-slate-900 transition-colors">+90 (212) 281 40 47</span>
              </li>
              <li className="flex items-center gap-3 group">
                <Mail size={18} className="text-odtu-red shrink-0 group-hover:scale-110 transition-transform" />
                <span className="group-hover:text-slate-900 transition-colors">dernek@odtumist.org</span>
              </li>
            </ul>
          </div>
        </div>
        
        <div className="border-t border-gray-100 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
          <p className="text-sm text-gray-400">
            © {new Date().getFullYear()} İstanbul ODTÜ Mezunlar Derneği.
          </p>
          <div className="flex gap-6 text-xs text-gray-400 font-medium">
             <a href="#" className="hover:text-gray-600">Gizlilik Politikası</a>
             <a href="#" className="hover:text-gray-600">Kullanım Koşulları</a>
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
