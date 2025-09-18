class Subject {
    int id;
    String code;
    String name;
    String type; // LEC/LAB
    double weeklyHours;
    int yearLevelId;
    Integer programId;

    public Subject(int id, String code, String name, String type, double weeklyHours, int yearLevelId, Integer programId) {
        this.id = id;
        this.code = code;
        this.name = name;
        this.type = type;
        this.weeklyHours = weeklyHours;
        this.yearLevelId = yearLevelId;
        this.programId = programId;
    }

    public boolean isLab() {
        return type.equalsIgnoreCase("LAB");
    }
}