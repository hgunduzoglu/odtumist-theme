import React, { useState, useEffect } from 'react';
import { Menu, X, Heart, UserPlus, ChevronDown, Instagram, Linkedin, Facebook, Youtube } from 'lucide-react';
import { ViewState } from '../types';
import { NAV_ITEMS, SOCIAL_LINKS } from '../constants';

// Custom X (formerly Twitter) Icon
const XIcon = ({ size = 18 }: { size?: number }) => (
  <svg width={size} height={size} viewBox="0 0 24 24" fill="currentColor">
    <path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932 6.064-6.932zm-1.292 19.494h2.039L6.486 3.24H4.298l13.311 17.407z" />
  </svg>
);

interface NavbarProps {
  activeView: ViewState;
  onNavigate: (view: ViewState, params?: any) => void;
}

const Navbar: React.FC<NavbarProps> = ({ activeView, onNavigate }) => {
  const [isScrolled, setIsScrolled] = useState(false);
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
  const [activeDropdown, setActiveDropdown] = useState<string | null>(null);

  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 20);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const navClass = `fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
    isScrolled ? 'bg-white/95 backdrop-blur-md shadow-lg py-2' : 'bg-gradient-to-b from-black/70 to-transparent py-4'
  }`;

  const textColor = isScrolled ? 'text-slate-800' : 'text-white';
  const hoverColor = 'hover:text-odtu-red';

  const handleExternalLink = () => {
    window.open('https://fonzip.com/odtumist', '_blank');
  };

  const handleNavClick = (label: string, view?: string) => {
    if (view) {
      if (view.startsWith('ABOUT_')) {
        const tab = view.split('_')[1].toLowerCase();
        onNavigate(ViewState.ABOUT, { tab });
      } else if (view.startsWith('SOLIDARITY_')) {
        const section = view.split('_')[1].toLowerCase();
        onNavigate(ViewState.SOLIDARITY, { section });
      } else {
        onNavigate(ViewState[view as keyof typeof ViewState]);
      }
    } else {
      const upperLabel = label.toUpperCase();
      if (upperLabel === 'HAKKIMIZDA') onNavigate(ViewState.ABOUT);
      else if (upperLabel === 'ETKİNLİKLER') onNavigate(ViewState.EVENTS);
      else if (upperLabel === 'DAYANIŞMA') onNavigate(ViewState.SOLIDARITY);
      else if (upperLabel === 'İLETİŞİM') onNavigate(ViewState.CONTACT);
      else if (upperLabel === 'ÜYELİK') handleExternalLink();
    }
    
    setIsMobileMenuOpen(false);
    setActiveDropdown(null);
  };

  return (
    <nav className={navClass}>
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center">
          <div 
            className="flex items-center gap-2 cursor-pointer group"
            onClick={() => onNavigate(ViewState.HOME)}
          >
            <div className="flex flex-col">
              <span className={`font-bold leading-tight tracking-tight text-2xl ${textColor}`}>ODTÜMİST</span>
            </div>
          </div>

          <div className="hidden lg:flex items-center gap-6">
            {/* Header Social Bar */}
            <div className="flex items-center gap-3 border-r border-slate-300/30 pr-6 mr-2">
              <a href={SOCIAL_LINKS.instagram} target="_blank" rel="noreferrer" className={`${textColor} hover:text-odtu-red transition-colors`}><Instagram size={18} /></a>
              <a href={SOCIAL_LINKS.linkedin} target="_blank" rel="noreferrer" className={`${textColor} hover:text-odtu-red transition-colors`}><Linkedin size={18} /></a>
              <a href={SOCIAL_LINKS.x} target="_blank" rel="noreferrer" className={`${textColor} hover:text-odtu-red transition-colors`}><XIcon size={16} /></a>
              <a href={SOCIAL_LINKS.facebook} target="_blank" rel="noreferrer" className={`${textColor} hover:text-odtu-red transition-colors`}><Facebook size={18} /></a>
              <a href={SOCIAL_LINKS.youtube} target="_blank" rel="noreferrer" className={`${textColor} hover:text-odtu-red transition-colors`}><Youtube size={18} /></a>
            </div>

            {NAV_ITEMS.map((item) => (
              <div 
                key={item.label} 
                className="relative group/menu"
                onMouseEnter={() => item.subItems.length > 0 && setActiveDropdown(item.label)}
                onMouseLeave={() => setActiveDropdown(null)}
              >
                <button 
                  className={`py-2 flex items-center gap-1 text-xs font-bold uppercase tracking-wide transition-colors ${textColor} ${hoverColor} border-b-2 border-transparent hover:border-odtu-red ${
                    activeView === (item.label === 'ÜYELİK' ? ViewState.MEMBERSHIP_WHY : 
                      item.label === 'HAKKIMIZDA' ? ViewState.ABOUT :
                      item.label === 'ETKİNLİKLER' ? ViewState.EVENTS :
                      item.label === 'DAYANIŞMA' ? ViewState.SOLIDARITY :
                      item.label === 'İLETİŞİM' ? ViewState.CONTACT : '')
                      ? 'border-odtu-red text-odtu-red' : ''
                  }`}
                  onClick={() => item.subItems.length === 0 && handleNavClick(item.label)}
                >
                  {item.label}
                  {item.subItems.length > 0 && <ChevronDown size={14} className={`transition-transform duration-300 ${activeDropdown === item.label ? 'rotate-180' : ''}`} />}
                </button>

                {item.subItems.length > 0 && (
                  <div className={`absolute top-full left-0 w-64 bg-white rounded-xl shadow-2xl border border-gray-100 py-3 transition-all duration-300 transform origin-top ${
                    activeDropdown === item.label ? 'opacity-100 scale-100' : 'opacity-0 scale-95 pointer-events-none'
                  }`}>
                    {item.subItems.map((sub) => (
                      <button
                        key={sub.label}
                        onClick={() => handleNavClick(item.label, sub.view)}
                        className="w-full text-left px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-odtu-red transition-colors"
                      >
                        {sub.label}
                      </button>
                    ))}
                  </div>
                )}
              </div>
            ))}

            <div className="flex items-center gap-3 ml-2">
              <button 
                onClick={() => onNavigate(ViewState.SOLIDARITY)} 
                className={`flex items-center gap-2 px-4 py-2 rounded-full border-2 font-bold transition-all text-[11px] uppercase tracking-wider
                  ${isScrolled 
                    ? 'border-odtu-red text-odtu-red hover:bg-odtu-red hover:text-white' 
                    : 'border-white text-white hover:bg-white hover:text-odtu-red'}`}
              >
                <Heart size={14} className={isScrolled ? "fill-current" : ""} />
                Bağış Yap
              </button>
              
              <button 
                onClick={handleExternalLink}
                className="flex items-center gap-2 px-5 py-2 bg-odtu-blue text-white rounded-full text-[11px] font-bold uppercase tracking-wider hover:bg-blue-800 transition-colors shadow-lg hover:shadow-blue-900/30"
              >
                <UserPlus size={14} />
                Üye Ol
              </button>
            </div>
          </div>

          <div className="lg:hidden">
            <button 
              onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
              className={`p-2 rounded-lg ${textColor}`}
            >
              {isMobileMenuOpen ? <X size={28} /> : <Menu size={28} />}
            </button>
          </div>
        </div>
      </div>

      {isMobileMenuOpen && (
        <div className="lg:hidden bg-white absolute top-full left-0 w-full h-screen border-t border-gray-100 shadow-xl py-4 px-4 flex flex-col gap-2 overflow-y-auto pb-20">
           {NAV_ITEMS.map((item) => (
              <div key={item.label} className="border-b border-gray-100 pb-2">
                <div className="flex justify-between items-center">
                  <button 
                    className="w-full text-left p-3 font-bold text-lg text-slate-900"
                    onClick={() => item.subItems.length === 0 && handleNavClick(item.label)}
                  >
                    {item.label}
                  </button>
                </div>
                {item.subItems.length > 0 && (
                  <div className="pl-6 flex flex-col gap-2 mt-2">
                    {item.subItems.map(sub => (
                      <button
                        key={sub.label}
                        onClick={() => handleNavClick(item.label, sub.view)}
                        className="text-left py-2 text-slate-500 font-medium"
                      >
                        {sub.label}
                      </button>
                    ))}
                  </div>
                )}
              </div>
           ))}
           <div className="mt-4 flex flex-col gap-3">
              <button 
                onClick={() => handleNavClick('DAYANIŞMA')}
                className="flex items-center justify-center gap-2 p-4 bg-odtu-red text-white rounded-xl font-bold"
              >
                <Heart size={18} />
                Bağış Yap
              </button>
              <button 
                onClick={handleExternalLink}
                className="flex items-center justify-center gap-2 p-4 bg-odtu-blue text-white rounded-xl font-bold"
              >
                <UserPlus size={18} />
                Üye Ol
              </button>
              <div className="flex justify-center gap-6 py-6 border-t border-gray-100">
                <a href={SOCIAL_LINKS.instagram} target="_blank" rel="noreferrer" className="text-slate-600 hover:text-odtu-red"><Instagram size={24} /></a>
                <a href={SOCIAL_LINKS.linkedin} target="_blank" rel="noreferrer" className="text-slate-600 hover:text-odtu-red"><Linkedin size={24} /></a>
                <a href={SOCIAL_LINKS.x} target="_blank" rel="noreferrer" className="text-slate-600 hover:text-odtu-red"><XIcon size={22} /></a>
                <a href={SOCIAL_LINKS.facebook} target="_blank" rel="noreferrer" className="text-slate-600 hover:text-odtu-red"><Facebook size={24} /></a>
                <a href={SOCIAL_LINKS.youtube} target="_blank" rel="noreferrer" className="text-slate-600 hover:text-odtu-red"><Youtube size={24} /></a>
              </div>
           </div>
        </div>
      )}
    </nav>
  );
};

export default Navbar;