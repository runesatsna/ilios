describe("ilios.cim.model", function () {

    it("should create a cim.model namespace on the ilios global object", function () {
        expect(typeof ilios.cim.model).toBe("object");
    });

    describe("ilios.cim.model.BaseModel", function () {

        it("should be an constructor function", function () {
            var obj = new ilios.cim.model.BaseModel();
            expect(typeof ilios.cim.model.BaseModel).toBe("function");
            expect(typeof obj).toBe("object");
            expect(obj instanceof ilios.cim.model.BaseModel).toEqual(true);
        });

        describe('generateClientId()', function () {

            it("should be a method", function () {
                expect(typeof ilios.cim.model.BaseModel.prototype.generateClientId).toBe("function");
            });

            it('should return a different value on each call', function () {
                var id1 = ilios.cim.model.BaseModel.prototype.generateClientId();
                var id2 = ilios.cim.model.BaseModel.prototype.generateClientId();
                var id3 = ilios.cim.model.BaseModel.prototype.generateClientId();
                expect(id1).not.toBe(id2);
                expect(id1).not.toBe(id3);
                expect(id2).not.toBe(id3);
            });
        })

        describe("getName()", function () {

           it("should be a method", function () {
               expect(typeof ilios.cim.model.BaseModel.prototype.getName).toBe("function");
           });

           it('should return "baseModel"', function () {
               var obj = new ilios.cim.model.BaseModel();
               expect(obj.getName()).toEqual("baseModel");
           });
        });

        describe("getClientId()", function () {

            it("should be a method", function () {
                expect(typeof ilios.cim.model.BaseModel.prototype.getClientId).toBe("function");
            });

            it("should return a value starting with the model name, followed by an underscore", function () {
                var obj = new ilios.cim.model.BaseModel();
                var name = obj.getName();
                expect(obj.getClientId().indexOf(name + "_")).toEqual(0);
            });

            it("should return an unique value per instance, containing an incremental suffix", function () {
                var obj1 = new ilios.cim.model.BaseModel();
                var obj2 = new ilios.cim.model.BaseModel();
                var obj3 = new ilios.cim.model.BaseModel();
                expect(obj1.getClientId().localeCompare(obj2.getClientId())).toBeLessThan(0);
                expect(obj2.getClientId().localeCompare(obj3.getClientId())).toBeLessThan(0);
            });
        });

        describe("getId()", function () {

            it("should be a method", function () {
                expect(typeof ilios.cim.model.BaseModel.prototype.getId).toBe("function");
            });

            it("should return the model's 'id' attribute, if set", function () {
                var obj = new ilios.cim.model.BaseModel({ 'dbId': 10 });
                expect(obj.getId()).toEqual(10);
            });

            it("should return NULL, if not set", function () {
                var obj = new ilios.cim.model.BaseModel();
                expect(obj.getId()).toBeNull();
            });
        });
    });

    describe('ilios.com.model.ObjectMap', function () {

        it("should be an constructor function", function () {
            var map = new ilios.cim.model.ObjectMap();
            expect(typeof ilios.cim.model.ObjectMap).toBe("function");
            expect(typeof map).toBe("object");
            expect(map instanceof ilios.cim.model.ObjectMap).toEqual(true);
        });

        describe("size()", function () {

            it("should be a method", function () {
                expect(typeof ilios.cim.model.ObjectMap.prototype.size).toBe("function");
            });

            it("should return the current number of objects in the map", function () {
                var map = new ilios.cim.model.ObjectMap();
                expect(map.size()).toEqual(0); // should return 0 if the map is empty.

                var obj1 = { "id": "foo" };
                var obj2 = { "id": "bar" };

                // add objects, see the return value of size() go up accordingly.
                map.add(obj1);
                expect(map.size()).toEqual(1);
                map.add(obj2);
                expect(map.size()).toEqual(2);

                // remove objects from the map, see the number go down again.

                map.remove('foo');
                expect(map.size()).toEqual(1);
                map.remove('bar');
                expect(map.size()).toEqual(0);
            });
        });

        describe("exists()", function () {

            it("should be a method", function () {
                expect(typeof ilios.cim.model.ObjectMap.prototype.exists).toBe("function");
            });

            it("should return FALSE if the map does not contain an object under a given key", function () {
                var map = new ilios.cim.model.ObjectMap();
                expect(map.exists("foo")).toBe(false);
            });

            it("should return TRUE if the map does not contain an object under a given key", function () {
                var map = new ilios.cim.model.ObjectMap();
                map.add({ "id": "foo" });
                expect(map.exists("foo")).toBe(true);
            });
        });

        describe("get()", function () {

            it("should be a method", function () {
                expect(typeof ilios.cim.model.ObjectMap.prototype.get).toBe("function");
            });

            it("should retrieve an object from the map by it's id", function () {
                var map = new ilios.cim.model.ObjectMap();
                var obj = { "id": "foo" };
                map.add(obj);
                expect(map.get("foo")).toBe(obj);
            });

            it("should raise an error if no object exists in the map under the given key", function () {
                var map = new ilios.cim.model.ObjectMap();
                expect(function(){ map.get("foo") }).toThrow();
            });

            it("should raise an error if no key was given", function () {
                var map = new ilios.cim.model.ObjectMap();
                expect(function(){ map.get() }).toThrow();
            });
        });

        describe("add()", function () {

            it("should be a method", function () {
                expect(typeof ilios.cim.model.ObjectMap.prototype.add).toBe("function");
            });

            it("should add a given object to the map", function () {
                var map = new ilios.cim.model.ObjectMap();
                var obj = { "id": "foo" };
                map.add(obj);
                expect(map.exists("foo")).toBe(true);
                expect(map.get("foo")).toBe(obj);
            });

            it("should return the given object", function () {
                var map = new ilios.cim.model.ObjectMap();
                var obj = { "id": "foo" };
                expect(map.add(obj)).toBe(obj);
            });

            it("should raise an error if the given object already exists in the map", function () {
                var map = new ilios.cim.model.ObjectMap();
                var obj = { "id": "foo" };
                var obj2 = { "id": "foo" };
                map.add(obj);
                expect(function(){ map.add(obj2) }).toThrow();
            });
        });

        describe("remove()", function () {
            // @todo pending implementation.
        });

        xdescribe("list()", function () {
            // @todo pending implementation.
        });

        xdescribe("walk()", function () {
            // @todo pending implementation.
        });
    })
});
