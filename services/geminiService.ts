
import { GoogleGenAI } from "@google/genai";
import { SYSTEM_INSTRUCTION } from '../constants';

let chatSession: any = null;

export const initializeChat = () => {
  try {
    const apiKey = process.env.API_KEY;
    if (!apiKey) {
      console.error("API Key not found");
      return null;
    }

    const ai = new GoogleGenAI({ apiKey });
    /* Updated model to 'gemini-3-flash-preview' for basic text tasks */
    chatSession = ai.chats.create({
      model: 'gemini-3-flash-preview',
      config: {
        systemInstruction: SYSTEM_INSTRUCTION,
        temperature: 0.7,
      }
    });
    return chatSession;
  } catch (error) {
    console.error("Failed to initialize chat", error);
    return null;
  }
};

export const sendMessageToGemini = async (message: string): Promise<string> => {
  if (!chatSession) {
    const initialized = initializeChat();
    if (!initialized) {
      return "Üzgünüm, şu anda bağlantı kuramıyorum. Lütfen daha sonra tekrar deneyin.";
    }
  }

  try {
    const result = await chatSession.sendMessage({ message });
    return result.text || "Bir cevap oluşturulamadı.";
  } catch (error) {
    console.error("Error sending message:", error);
    return "Bir hata oluştu. Lütfen tekrar deneyin.";
  }
};

export const generateHeroCollage = async (): Promise<string | null> => {
  try {
    const apiKey = process.env.API_KEY;
    if (!apiKey) return null;

    const ai = new GoogleGenAI({ apiKey });
    const prompt = `A nostalgic, asymmetric, tight collage for ODTÜ (Middle East Technical University) alumni in Istanbul. 
    The collage MUST include: 
    1. The 'Bilim Ağacı' (Science Tree) sculpture.
    2. The word 'DEVRİM' written on stadium steps.
    3. A vintage blue 'ring' campus bus.
    4. The iconic red 'Et Arabası' vintage bus from ODTÜPARK.
    5. The 'Avarel' statue.
    6. Wooden 'baraka' campus buildings.
    7. The distinct concrete architectural poles of the METU Faculty of Architecture.
    8. A silhouette or stylized view of the Bosphorus Bridge (Istanbul).
    
    Style requirements: 
    - Non-digital, artistic look. 
    - Resembles vintage postcards or crinkled, aged paper textures.
    - Images should have distinct, slightly irregular frames or borders.
    - No humans. 
    - Asymmetric and crowded placement (collage style).
    - Muted, warm, nostalgic color palette.`;

    const response = await ai.models.generateContent({
      model: 'gemini-2.5-flash-image',
      contents: { parts: [{ text: prompt }] },
      config: {
        imageConfig: {
          aspectRatio: "16:9"
        }
      }
    });

    for (const part of response.candidates?.[0]?.content?.parts || []) {
      if (part.inlineData) {
        return `data:image/png;base64,${part.inlineData.data}`;
      }
    }
    return null;
  } catch (error) {
    console.error("Failed to generate image:", error);
    return null;
  }
};

export const generateSimpleScienceTree = async (): Promise<string | null> => {
  try {
    const apiKey = process.env.API_KEY;
    if (!apiKey) return null;

    const ai = new GoogleGenAI({ apiKey });
    const prompt = `A simple, elegant and clean architectural photograph of the ODTÜ Science Tree (Bilim Ağacı) sculpture. 
    Requirements:
    - Focus only on the 'Bilim Ağacı' sculpture.
    - Clear, minimalist background (can be a clear blue sky or soft campus sunset).
    - Professional, high-quality aesthetic.
    - No collage, no extra elements, no humans.
    - Sophisticated, sharp and realistic style.`;

    const response = await ai.models.generateContent({
      model: 'gemini-2.5-flash-image',
      contents: { parts: [{ text: prompt }] },
      config: {
        imageConfig: {
          aspectRatio: "16:9"
        }
      }
    });

    for (const part of response.candidates?.[0]?.content?.parts || []) {
      if (part.inlineData) {
        return `data:image/png;base64,${part.inlineData.data}`;
      }
    }
    return null;
  } catch (error) {
    console.error("Failed to generate image:", error);
    return null;
  }
};

export const generateMentorGraphic = async (): Promise<string | null> => {
  try {
    const apiKey = process.env.API_KEY;
    if (!apiKey) return null;

    const ai = new GoogleGenAI({ apiKey });
    const prompt = `A professional, clean, flat-design infographic background for a Mentorship program. 
    Colors: Deep ODTÜ Blue (#00529B) and ODTÜ Red (#E31E24).
    The design should feature 6 stylized minimalist line-art icons representing these concepts:
    1. A map/path icon (Birlikte Yol Al)
    2. A circular arrow icon (Öğrenirken Dönüş)
    3. A globe network icon (Ağın Gücünü Keşfet)
    4. A target/arrow icon (Potansiyelini Harekete Geçir)
    5. A stylized human figure with arms wide (ODTÜ Ruhu Yaşasın)
    6. A puzzle piece icon (Paylaş ve Güçlen)
    
    Layout: Symmetrical grid or elegant floating arrangement on a clean white/off-white background.
    Style: Minimalist, corporate, modern, no stock photos, no human faces. 
    High contrast and professional look.`;

    const response = await ai.models.generateContent({
      model: 'gemini-2.5-flash-image',
      contents: { parts: [{ text: prompt }] },
      config: {
        imageConfig: {
          aspectRatio: "16:9"
        }
      }
    });

    for (const part of response.candidates?.[0]?.content?.parts || []) {
      if (part.inlineData) {
        return `data:image/png;base64,${part.inlineData.data}`;
      }
    }
    return null;
  } catch (error) {
    console.error("Failed to generate image:", error);
    return null;
  }
};
