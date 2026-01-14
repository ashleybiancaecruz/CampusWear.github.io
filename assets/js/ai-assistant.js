const aiResponses = {
    greetings: ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening'],
    farewells: ['bye', 'goodbye', 'see you', 'thanks', 'thank you'],
    help: ['help', 'what can you do', 'how can you help', 'what do you do'],
    price: ['price', 'cost', 'how much', 'expensive', 'cheap', 'affordable'],
    stock: ['stock', 'available', 'in stock', 'out of stock', 'quantity'],
    organization: ['organization', 'org', 'society', 'club'],
    discount: ['discount', 'sale', 'promo', 'promotion', 'isc discount', 'member discount'],
    order: ['order', 'purchase', 'buy', 'checkout', 'cart'],
    shipping: ['shipping', 'delivery', 'address', 'when will it arrive', 'pickup', 'pick-up']
};

const outOfScopeKeywords = [
    'weather', 'news', 'sports', 'politics', 'movie', 'music', 'game', 'recipe', 'cooking',
    'travel', 'hotel', 'flight', 'restaurant', 'food', 'health', 'medical', 'doctor',
    'school', 'university', 'class', 'exam', 'homework', 'assignment', 'grade', 'course',
    'job', 'career', 'interview', 'resume', 'salary', 'work', 'employment'
];

function isOutOfScope(query) {
    return outOfScopeKeywords.some(keyword => query.includes(keyword));
}

function isMerchandiseRelated(query, merchandiseData) {
    const campuswearKeywords = ['campuswear', 'campus wear', 'merchandise', 'product', 'item', 'merch', 'buy', 'purchase', 'order', 'cart', 'checkout', 'website', 'site'];
    const orgNames = merchandiseData.map(m => m.org_name.toLowerCase());
    const productNames = merchandiseData.map(m => m.name.toLowerCase());
    
    const hasCampuswearKeyword = campuswearKeywords.some(kw => query.includes(kw));
    const hasOrgName = orgNames.some(org => query.includes(org));
    const hasProductName = productNames.some(name => {
        const words = name.split(' ');
        return words.some(word => word.length > 3 && query.includes(word));
    });
    
    return hasCampuswearKeyword || hasOrgName || hasProductName;
}

function generateAIResponse(userText, merchandiseData) {
    if (!merchandiseData || merchandiseData.length === 0) {
        return 'I apologize, but I am currently unable to access merchandise data. Please refresh the page and try again.';
    }
    
    const query = userText.toLowerCase().trim();
    
    if (isOutOfScope(query) && !isMerchandiseRelated(query, merchandiseData)) {
        return 'I apologize, but I can only answer questions related to CampusWear merchandise, products, organizations, pricing, and website functionality. Your question is outside the scope of this website. Please ask me about our merchandise, products, or how to use the website.';
    }
    
    let response = '';
    
    if (aiResponses.greetings.some(g => query.includes(g))) {
        response = `Hello! I am your CampusWear assistant. I can help you find merchandise, check prices, and answer questions about our products and organizations. What are you looking for today?`;
    }
    else if (aiResponses.farewells.some(f => query.includes(f))) {
        response = `You are welcome! Feel free to come back if you need any help. Happy shopping!`;
    }
    else if (aiResponses.help.some(h => query.includes(h))) {
        response = `I can help you with finding merchandise, checking prices, organization information, ISC membership discounts, and order inquiries. Just ask me anything about our products or website!`;
    }
    else if (aiResponses.price.some(p => query.includes(p))) {
        const prices = merchandiseData.map(m => parseFloat(m.price));
        const minPrice = Math.min(...prices);
        const maxPrice = Math.max(...prices);
        response = `Our merchandise prices range from PHP ${minPrice.toFixed(2)} to PHP ${maxPrice.toFixed(2)}. You can filter by organization to see specific items and their prices.`;
    }
    else if (aiResponses.stock.some(s => query.includes(s))) {
        const inStock = merchandiseData.filter(m => m.stock > 0).length;
        const outOfStock = merchandiseData.filter(m => m.stock === 0).length;
        response = `Currently, we have ${inStock} items in stock and ${outOfStock} items out of stock. Each product card shows its stock status.`;
    }
    else if (aiResponses.organization.some(o => query.includes(o))) {
        const orgs = [...new Set(merchandiseData.map(m => m.org_name))];
        response = `We have merchandise from ${orgs.length} organizations including ${orgs.slice(0, 5).join(', ')} and others. Which organization are you interested in?`;
    }
    else if (aiResponses.discount.some(d => query.includes(d))) {
        response = `ISC Members get special 10% discounts on ISC merchandise. If you are an ISC member, you will automatically see discounted prices when you checkout. You can apply for membership by clicking "Apply Now" on the homepage.`;
    }
    else if (aiResponses.order.some(o => query.includes(o))) {
        response = `To place an order, browse our merchandise and click "Buy Now" on any item. Select quantity, verify your phone number with OTP, and complete payment via PayPal. You can view your purchase history anytime.`;
    }
    else if (aiResponses.shipping.some(s => query.includes(s))) {
        response = `All orders are for pickup at the COMSOC Office, New Building, 3rd Floor, Room 304. Pickup dates vary by product type (typically 7-30 business days). The estimated pickup date is shown during checkout.`;
    }
    else {
        const isHoodieQuery = query.includes('hoodie');
        const orgNames = [...new Set(merchandiseData.map(m => m.org_name.toLowerCase()))];
        const isSpecificOrg = orgNames.some(org => query.includes(org));
        
        if (isHoodieQuery && !isSpecificOrg) {
            const hoodies = merchandiseData.filter(m => m.name.toLowerCase().includes('hoodie'));
            if (hoodies.length > 0) {
                const hoodieList = hoodies.slice(0, 3).map(m => `${m.name} from ${m.org_name} at PHP ${parseFloat(m.price).toFixed(2)}`).join(', ');
                response = `Here are some available hoodies: ${hoodieList}.${hoodies.length > 3 ? ` And ${hoodies.length - 3} more.` : ''}`;
            } else {
                response = `We currently do not have any hoodies in stock.`;
            }
        } else {
            const foundMerch = merchandiseData.find(m => 
                m.name.toLowerCase().includes(query) || 
                m.description.toLowerCase().includes(query) ||
                m.org_name.toLowerCase().includes(query)
            );
            
            if (foundMerch) {
                const stockStatus = foundMerch.stock > 10 ? 'In Stock' : foundMerch.stock > 0 ? `Low Stock (${foundMerch.stock} left)` : 'Out of Stock';
                response = `I found "${foundMerch.name}" from ${foundMerch.org_name}. Description: ${foundMerch.description}. Price: PHP ${parseFloat(foundMerch.price).toFixed(2)}. Stock Status: ${stockStatus}.`;
            } else {
                const partialMatches = merchandiseData.filter(m => 
                    m.name.toLowerCase().split(' ').some(word => word.length > 3 && query.includes(word)) ||
                    m.org_name.toLowerCase().includes(query)
                );
                
                if (partialMatches.length > 0) {
                    response = `I found ${partialMatches.length} similar item${partialMatches.length > 1 ? 's' : ''}. For example: "${partialMatches[0].name}" from ${partialMatches[0].org_name} at PHP ${parseFloat(partialMatches[0].price).toFixed(2)}.`;
                } else {
                    response = `I apologize, but I could not find any merchandise matching your request. Please try searching for a specific product name or organization. I can only help with questions about CampusWear merchandise and website functionality.`;
                }
            }
        }
    }
    
    return response;
}

function askAIEnhanced() {
    const input = document.getElementById('aiInput');
    const chat = document.getElementById('chatContent');
    
    if (!input || !chat) {
        console.error('AI elements not found');
        return;
    }
    
    const userText = input.value.trim();
    
    if (!userText) return;
    
    input.disabled = true;
    
    const userMsg = document.createElement('div');
    userMsg.className = 'bg-primary text-white p-3 rounded-4 ms-auto small shadow-sm mb-2';
    userMsg.style.maxWidth = '85%';
    userMsg.style.fontFamily = "'Poppins', sans-serif";
    userMsg.style.fontWeight = "500";
    userMsg.textContent = userText;
    chat.appendChild(userMsg);
    input.value = '';
    input.disabled = false;
    chat.scrollTop = chat.scrollHeight;
    
    const typingId = 'typing-' + Date.now();
    const typingMsg = document.createElement('div');
    typingMsg.id = typingId;
    typingMsg.className = 'bg-white p-3 rounded-4 border small shadow-sm me-auto mb-2';
    typingMsg.style.maxWidth = '85%';
    typingMsg.style.fontFamily = "'Poppins', sans-serif";
    typingMsg.style.fontWeight = "500";
    typingMsg.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Thinking...';
    chat.appendChild(typingMsg);
    chat.scrollTop = chat.scrollHeight;
    
    setTimeout(() => {
        const merchData = typeof merchandiseData !== 'undefined' ? merchandiseData : window.merchandiseData;
        
        if (!merchData || !Array.isArray(merchData) || merchData.length === 0) {
            const aiMsg = document.createElement('div');
            aiMsg.className = 'bg-white p-3 rounded-4 border small shadow-sm me-auto mb-2';
            aiMsg.style.maxWidth = '85%';
            aiMsg.style.fontFamily = "'Poppins', sans-serif";
            aiMsg.style.fontWeight = "500";
            aiMsg.innerHTML = 'I\'m having trouble accessing the merchandise data. Please refresh the page.';
            const typingEl = document.getElementById(typingId);
            if (typingEl) typingEl.remove();
            chat.appendChild(aiMsg);
            chat.scrollTop = chat.scrollHeight;
            return;
        }
        
        const response = generateAIResponse(userText, merchData);
        const aiMsg = document.createElement('div');
        aiMsg.className = 'bg-white p-3 rounded-4 border small shadow-sm me-auto mb-2';
        aiMsg.style.maxWidth = '85%';
        aiMsg.style.fontFamily = "'Poppins', sans-serif";
        aiMsg.style.fontWeight = "500";
        aiMsg.innerHTML = response.replace(/\n/g, '<br>');
        
        const typingEl = document.getElementById(typingId);
        if (typingEl) typingEl.remove();
        chat.appendChild(aiMsg);
        chat.scrollTop = chat.scrollHeight;
        input.focus();
    }, 300 + Math.random() * 200);
}

function askAI() {
    if (typeof askAIEnhanced === 'function') {
        askAIEnhanced();
    } else {
        console.error('AI function not available');
    }
}

function toggleAI() {
    const aiWindow = document.getElementById('aiWindow');
    if (aiWindow) {
        const isOpening = !aiWindow.classList.contains('active');
        aiWindow.classList.toggle('active');
        if (isOpening) {
            const chat = document.getElementById('chatContent');
            const userName = typeof currentUserName !== 'undefined' ? currentUserName : 'there';
            const greeting = document.createElement('div');
            greeting.className = 'bg-white p-3 rounded-4 border small shadow-sm me-auto mb-2';
            greeting.style.maxWidth = '85%';
            greeting.style.fontFamily = "'Poppins', sans-serif";
            greeting.style.fontWeight = "500";
            greeting.innerHTML = `Hello ${userName}. I am your CampusWear assistant. I can help you find merchandise, check prices, answer questions about our products, and assist with orders. What can I help you with today?`;
            chat.innerHTML = '';
            chat.appendChild(greeting);
            chat.scrollTop = chat.scrollHeight;
            document.getElementById('aiInput')?.focus();
        }
    }
}