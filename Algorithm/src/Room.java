class Room {
    int id;
    String name;
    String type;

    public Room(int id, String name, String type) {
        this.id = id;
        this.name = name;
        this.type = type;
    }

    public boolean typeMatches(Subject subject) {
        return (subject.isLab() && type.equalsIgnoreCase("Lab")) ||
                (!subject.isLab() && type.equalsIgnoreCase("Lecture"));
    }
}