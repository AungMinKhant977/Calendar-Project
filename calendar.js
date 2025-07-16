const calendarEl = document.getElementById("calendar");
const monthYearEl = document.getElementById("monthYear");
const modalEl = document.getElementById("eventModal");
let currentDate = new Date(); 

function renderCalendar(date = new Date()) {
    console.log("renderCalendar function called for date:", date); 
    calendarEl.innerHTML = ''; 

    const year = date.getFullYear();    
    const month = date.getMonth();      
    const today = new Date();           

    
    const totalDays = new Date(year, month + 1, 0).getDate();
    
    const firstDayOfMonth = new Date(year, month, 1).getDay();

    
    monthYearEl.textContent = date.toLocaleDateString("en-US", {
        month: 'long', 
        year: 'numeric' 
    });

    
    const weekDays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    weekDays.forEach(day => {
        const dayEl = document.createElement("div");
        dayEl.className = "day-name";
        dayEl.textContent = day;
        calendarEl.appendChild(dayEl);
    });

    
    for (let i = 0; i < firstDayOfMonth; i++) {
        calendarEl.appendChild(document.createElement("div"));
        console.log("Adding empty day div before 1st."); 
    }

    
    for (let day = 1; day <= totalDays; day++) {
        
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        console.log("Processing day:", dateStr); // Debugging

        const cell = document.createElement("div");
        cell.className = "day"; 

        
        if (
            day === today.getDate() && 
            month === today.getMonth() &&
            year === today.getFullYear()
        ) {
            cell.classList.add("today");
        }

        const dateEl = document.createElement("div");
        dateEl.className = "date-number";
        dateEl.textContent = day; 
        cell.appendChild(dateEl);


        const eventsToday = events.filter(e => e.date === dateStr);
        const eventBox = document.createElement("div");
        eventBox.className = "events";

        
        eventsToday.forEach(event => {
            const ev = document.createElement("div");
            ev.className = "event"; 

            const courseEl = document.createElement("div");
            courseEl.className = "course";
            courseEl.textContent = event.title.split(" - ")[0]; 

            const instructorEl = document.createElement("div");
            instructorEl.className = "instructor";
            instructorEl.textContent = "👨🏽‍🏫 " + (event.title.split(" - ")[1] || ''); 

            const timeEl = document.createElement("div"); 
            timeEl.className = "time";
            timeEl.textContent = "⏰ " + event.start_time + " - " + event.end_time; 

            ev.appendChild(courseEl);
            ev.appendChild(instructorEl);
            ev.appendChild(timeEl);
            eventBox.appendChild(ev);
        });

        
        const overlay = document.createElement("div"); 
        overlay.className = "day-overlay";

        const addBtn = document.createElement("button");
        addBtn.className = "overlay-btn add-btn"; 
        addBtn.textContent = "+ Add";
        addBtn.onclick = e => {
            e.stopPropagation(); 
            openModalForAdd(dateStr); 
        };
        overlay.appendChild(addBtn);

        
        if (eventsToday.length > 0) {
            const editBtn = document.createElement("button");
            editBtn.className = "overlay-btn edit-btn"; 
            editBtn.textContent = "Edit";
            editBtn.onclick = e => {
                e.stopPropagation(); 
                openModalForEdit(eventsToday); 
            };
            overlay.appendChild(editBtn);
        }

        cell.appendChild(overlay);
        cell.appendChild(eventBox);
        calendarEl.appendChild(cell); 
    }
}


function openModalForAdd(dateStr) {
    document.getElementById("formAction").value = "add";        
    document.getElementById("eventId").value = "";              
    document.getElementById("deleteEventId").value = "";        
    document.getElementById("courseName").value = "";           
    document.getElementById("instructorName").value = "";
    document.getElementById("startDate").value = dateStr;       
    document.getElementById("endDate").value = dateStr;         
    document.getElementById("startTime").value = "09:00";       
    document.getElementById("endTime").value = "10:00";         

    const selector = document.getElementById("eventSelector");
    const wrapper = document.getElementById("eventSelectorWrapper");
    if (selector && wrapper) {
        selector.innerHTML = "<option value='' disabled selected>Choose event...</option>"; 
        wrapper.style.display = "none"; 
    }

    modalEl.style.display = "flex";
}


function openModalForEdit(eventsOnDate) {
    document.getElementById("formAction").value = "edit";
    modalEl.style.display = "flex";

    const selector = document.getElementById("eventSelector");
    const wrapper = document.getElementById("eventSelectorWrapper");
    selector.innerHTML = "<option value='' disabled selected>Choose event...</option>";

    
    eventsOnDate.forEach(e => {
        const option = document.createElement("option");
        option.value = JSON.stringify(e); 
        option.textContent = `${e.title} (${e.start_time} - ${e.end_time})`;
        selector.appendChild(option);
    });

    
    if (eventsOnDate.length > 1) {
        wrapper.style.display = "block";
    } else {
        wrapper.style.display = "none";
    }

    
    if (eventsOnDate.length > 0) {
        if (eventsOnDate.length === 1) {
            selector.value = JSON.stringify(eventsOnDate[0]);
        }
        handleEventSelection(JSON.stringify(eventsOnDate[0]));
    }
}


function handleEventSelection(eventJSON) {
    const event = JSON.parse(eventJSON); 

    document.getElementById("eventId").value = event.id;       
    document.getElementById("deleteEventId").value = event.id;  

    const [course, instructor] = event.title.split(" - ").map(e => e.trim());
    document.getElementById("courseName").value = course || "";
    document.getElementById("instructorName").value = instructor || "";
    document.getElementById("startDate").value = event.start || "";
    document.getElementById("endDate").value = event.end || "";
    document.getElementById("startTime").value = event.start_time || "";
    document.getElementById("endTime").value = event.end_time || "";
}

function closeModal() {
    modalEl.style.display = "none";
}

function changeMonth(offset) {
    currentDate.setMonth(currentDate.getMonth() + offset); 
    renderCalendar(currentDate);
}

function updateClock() {
    const now = new Date();
    const clock = document.getElementById("clock");
    if (clock) { 
        clock.textContent = [
            now.getHours().toString().padStart(2, "0"),   
            now.getMinutes().toString().padStart(2, "0"), 
            now.getSeconds().toString().padStart(2, "0"), 
        ].join(":");
    }
}


renderCalendar(currentDate);

updateClock();
setInterval(updateClock, 1000); 

document.querySelector(".nav-btn.prev-month").addEventListener("click", () => changeMonth(-1));
document.querySelector(".nav-btn.next-month").addEventListener("click", () => changeMonth(1));

document.getElementById("eventSelector").addEventListener("change", function() {
    handleEventSelection(this.value);
});

document.querySelector(".submit-btn.cancel-btn").addEventListener("click", closeModal);

const modalCloseButton = document.querySelector(".modal-content .close-button");
if (modalCloseButton) {
    modalCloseButton.addEventListener("click", closeModal);
}