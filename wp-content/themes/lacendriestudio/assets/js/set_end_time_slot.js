const TIME_TO_WAIT = 600;
const timeSlots = {
  10: "10h - 14h",
  14: "14h - 18h",
  18: "18h - 21h",
  21: "21h - 00h",
};

const addSubItemsEvent = () => {
  setTimeout(() => {
    const subItems = document.querySelectorAll(".sb_item");
    if (subItems.length === 0) {
      addSubItemsEvent();
    } else {
      for (const subItem of subItems) {
        const sub = subItem.querySelector("a");
        sub?.addEventListener("click", () => {
          setEndTimeSlots(true, true);
          addSubItemsEvent();
        });
      }
    }
  }, TIME_TO_WAIT);
};

const addDatePickerEvent = () => {
  setTimeout(() => {
    const days = document.querySelectorAll("tr td .ui-state-default");

    if (days.length === 0) {
      addDatePickerEvent();
    } else {
      for (const day of days) {
        day.addEventListener("click", () => {
          setEndTimeSlots(false, true);
          addDatePickerEvent();
        });
      }
    }
  }, TIME_TO_WAIT);
};

const addMonthPickerEvent = () => {
  setTimeout(() => {
    const months = document.querySelectorAll(
      ".ui-datepicker-header .ui-corner-all"
    );
    if (months.length === 0) {
      addMonthPickerEvent();
    } else {
      for (const month of months) {
        month.addEventListener("click", () => {
          addMonthPickerEvent();
          addDatePickerEvent();
        });
      }
    }
  }, TIME_TO_WAIT);
};

/**
 * @param {HTMLAnchorElement | null} link
 * @returns {string | void}
 */
const getTimeSlot = (link) => {
  const hourAttribute = link?.getAttribute("h1");

  const timeSlot = timeSlots[hourAttribute];
  if (!timeSlot) return;

  return timeSlot;
};

/**
 *
 * @param {boolean} initLoading
 * @param {boolean} monthLoading
 */
const setEndTimeSlots = (initLoading = false, monthLoading = false) => {
  setTimeout(() => {
    const startTimeSlots = document.querySelectorAll(
      ".slots .availableslot, .slots .usedslot"
    );

    if (startTimeSlots.length === 0) {
      setEndTimeSlots(initLoading, monthLoading);
    } else {
      for (const startTimeSlot of startTimeSlots) {
        const link = startTimeSlot.querySelector("a");
        let timeSlot = getTimeSlot(link);

        if (monthLoading) {
          addMonthPickerEvent();
        }

        if (initLoading) {
          addDatePickerEvent();
        }

        if (!timeSlot) {
          return;
        }
        if (link) {
          link.innerText = timeSlot;
          link.addEventListener("click", () => {
            addSubItemsEvent();
            setEndTimeSlots(false, false);
          });
        }
      }
    }
  }, TIME_TO_WAIT);
};

setEndTimeSlots(true, true);
