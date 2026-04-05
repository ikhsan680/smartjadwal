import { useState } from "react";
import { Plus, Edit, Trash2, BookOpen } from "lucide-react";

interface Subject {
  id: number;
  name: string;
  code: string;
  category: string;
  hours: number;
}

export function SubjectsPage() {
  const [subjects] = useState<Subject[]>([
    { id: 1, name: "Mathematics", code: "MTK", category: "Core", hours: 4 },
    { id: 2, name: "English", code: "ENG", category: "Core", hours: 3 },
    { id: 3, name: "Physics", code: "FIS", category: "Science", hours: 3 },
    { id: 4, name: "Chemistry", code: "KIM", category: "Science", hours: 3 },
    { id: 5, name: "Programming", code: "PRG", category: "Vocational", hours: 6 },
    { id: 6, name: "Database", code: "DBS", category: "Vocational", hours: 4 },
    { id: 7, name: "Web Design", code: "WEB", category: "Vocational", hours: 5 },
    { id: 8, name: "Computer Network", code: "NET", category: "Vocational", hours: 4 },
    { id: 9, name: "Project Management", code: "PM", category: "Vocational", hours: 3 },
    { id: 10, name: "Indonesian", code: "IND", category: "Core", hours: 3 },
    { id: 11, name: "History", code: "SEJ", category: "Social", hours: 2 },
    { id: 12, name: "Physical Education", code: "PJK", category: "Sports", hours: 2 },
  ]);

  const getCategoryColor = (category: string) => {
    switch (category) {
      case "Core":
        return "bg-blue-100 text-blue-800";
      case "Science":
        return "bg-green-100 text-green-800";
      case "Vocational":
        return "bg-purple-100 text-purple-800";
      case "Social":
        return "bg-orange-100 text-orange-800";
      case "Sports":
        return "bg-red-100 text-red-800";
      default:
        return "bg-gray-100 text-gray-800";
    }
  };

  return (
    <div>
      <div className="mb-8">
        <h1 className="text-3xl text-gray-900 mb-2">Subjects Management</h1>
        <p className="text-gray-600">Manage school subjects and curriculum</p>
      </div>

      <div className="mb-6 flex justify-end">
        <button className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
          <Plus className="w-5 h-5" />
          Add Subject
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {subjects.map((subject) => (
          <div key={subject.id} className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div className="flex items-start justify-between mb-4">
              <div className="flex items-start gap-3">
                <div className="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                  <BookOpen className="w-5 h-5 text-blue-600" />
                </div>
                <div>
                  <h3 className="text-lg text-gray-900">{subject.name}</h3>
                  <p className="text-sm text-gray-600">Code: {subject.code}</p>
                </div>
              </div>
              <div className="flex gap-2">
                <button className="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                  <Edit className="w-4 h-4" />
                </button>
                <button className="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                  <Trash2 className="w-4 h-4" />
                </button>
              </div>
            </div>

            <div className="space-y-3">
              <div className="flex items-center justify-between">
                <span className={`px-3 py-1 rounded-full text-xs ${getCategoryColor(subject.category)}`}>
                  {subject.category}
                </span>
                <span className="text-sm text-gray-600">{subject.hours} hours/week</span>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
