import { useState } from "react";
import { ChevronLeft, ChevronRight } from "lucide-react";

interface ScheduleEntry {
  class: string;
  subject: string;
  teacher: string;
  room: string;
}

type WeekSchedule = {
  [key: string]: {
    [timeSlot: string]: ScheduleEntry | null;
  };
};

export function CalendarPage() {
  const [selectedClass, setSelectedClass] = useState("X RPL 1");

  const timeSlots = [
    "07:30 - 09:00",
    "09:15 - 10:45",
    "11:00 - 12:30",
    "12:45 - 14:15",
    "14:30 - 16:00",
  ];

  const days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];

  const classes = ["X RPL 1", "X RPL 2", "XI RPL 1", "XI RPL 2", "XII RPL 1", "XII RPL 2"];

  const schedule: WeekSchedule = {
    "Monday": {
      "07:30 - 09:00": { class: "X RPL 1", subject: "Mathematics", teacher: "John Doe", room: "R101" },
      "09:15 - 10:45": { class: "X RPL 1", subject: "English", teacher: "Jane Smith", room: "R102" },
      "11:00 - 12:30": { class: "X RPL 1", subject: "Physics", teacher: "David Lee", room: "R103" },
      "12:45 - 14:15": { class: "X RPL 1", subject: "Programming", teacher: "Bob Wilson", room: "Lab 1" },
      "14:30 - 16:00": null,
    },
    "Tuesday": {
      "07:30 - 09:00": { class: "X RPL 1", subject: "Database", teacher: "Alice Brown", room: "Lab 2" },
      "09:15 - 10:45": { class: "X RPL 1", subject: "Web Design", teacher: "Charlie Davis", room: "Lab 1" },
      "11:00 - 12:30": { class: "X RPL 1", subject: "Chemistry", teacher: "Emma White", room: "Lab 3" },
      "12:45 - 14:15": null,
      "14:30 - 16:00": { class: "X RPL 1", subject: "Physical Education", teacher: "Frank Green", room: "Field" },
    },
    "Wednesday": {
      "07:30 - 09:00": { class: "X RPL 1", subject: "Indonesian", teacher: "John Doe", room: "R104" },
      "09:15 - 10:45": { class: "X RPL 1", subject: "Mathematics", teacher: "John Doe", room: "R101" },
      "11:00 - 12:30": { class: "X RPL 1", subject: "Programming", teacher: "Bob Wilson", room: "Lab 1" },
      "12:45 - 14:15": { class: "X RPL 1", subject: "Computer Network", teacher: "Charlie Davis", room: "Lab 2" },
      "14:30 - 16:00": null,
    },
    "Thursday": {
      "07:30 - 09:00": { class: "X RPL 1", subject: "English", teacher: "Jane Smith", room: "R102" },
      "09:15 - 10:45": { class: "X RPL 1", subject: "Database", teacher: "Alice Brown", room: "Lab 2" },
      "11:00 - 12:30": { class: "X RPL 1", subject: "History", teacher: "David Lee", room: "R105" },
      "12:45 - 14:15": { class: "X RPL 1", subject: "Web Design", teacher: "Charlie Davis", room: "Lab 1" },
      "14:30 - 16:00": null,
    },
    "Friday": {
      "07:30 - 09:00": { class: "X RPL 1", subject: "Project Management", teacher: "Frank Green", room: "R201" },
      "09:15 - 10:45": { class: "X RPL 1", subject: "Programming", teacher: "Bob Wilson", room: "Lab 1" },
      "11:00 - 12:30": null,
      "12:45 - 14:15": null,
      "14:30 - 16:00": null,
    },
  };

  return (
    <div>
      <div className="mb-8">
        <h1 className="text-3xl text-gray-900 mb-2">Schedule Calendar View</h1>
        <p className="text-gray-600">Weekly timetable overview</p>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-100 mb-6 p-6">
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div className="flex items-center gap-4">
            <label className="text-sm text-gray-700">Select Class:</label>
            <select
              value={selectedClass}
              onChange={(e) => setSelectedClass(e.target.value)}
              className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent outline-none"
            >
              {classes.map((cls) => (
                <option key={cls} value={cls}>{cls}</option>
              ))}
            </select>
          </div>
          <div className="flex items-center gap-2">
            <button className="p-2 hover:bg-gray-100 rounded-lg">
              <ChevronLeft className="w-5 h-5 text-gray-600" />
            </button>
            <span className="text-sm text-gray-700 px-4">Week 1 - March 2026</span>
            <button className="p-2 hover:bg-gray-100 rounded-lg">
              <ChevronRight className="w-5 h-5 text-gray-600" />
            </button>
          </div>
        </div>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full border-collapse">
            <thead>
              <tr className="bg-blue-600">
                <th className="px-4 py-3 text-left text-sm text-white w-32 sticky left-0 bg-blue-600 z-10">Time</th>
                {days.map((day) => (
                  <th key={day} className="px-4 py-3 text-center text-sm text-white min-w-[200px]">
                    {day}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody>
              {timeSlots.map((timeSlot, index) => (
                <tr key={timeSlot} className={index % 2 === 0 ? "bg-gray-50" : "bg-white"}>
                  <td className="px-4 py-3 text-sm text-gray-900 border-r border-gray-200 sticky left-0 z-10" style={{ backgroundColor: index % 2 === 0 ? "#f9fafb" : "white" }}>
                    {timeSlot}
                  </td>
                  {days.map((day) => {
                    const entry = schedule[day]?.[timeSlot];
                    return (
                      <td key={day} className="px-2 py-2 border-l border-gray-200">
                        {entry ? (
                          <div className="bg-blue-50 border-l-4 border-blue-600 rounded p-3 hover:shadow-md transition-shadow">
                            <div className="text-sm text-gray-900 mb-1">{entry.subject}</div>
                            <div className="text-xs text-gray-600">{entry.teacher}</div>
                            <div className="text-xs text-gray-500 mt-1">{entry.room}</div>
                          </div>
                        ) : (
                          <div className="h-20 flex items-center justify-center text-gray-400 text-xs">
                            -
                          </div>
                        )}
                      </td>
                    );
                  })}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      <div className="mt-6 bg-blue-50 border border-blue-100 rounded-xl p-4">
        <div className="flex items-start gap-2">
          <div className="text-sm text-blue-900">
            <span className="font-medium">Tip:</span> Click on any schedule cell to view details or make changes.
          </div>
        </div>
      </div>
    </div>
  );
}
